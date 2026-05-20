<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Document;
use Modules\CRM\Models\Lead;
use Modules\RealEstate\Models\Listing;

/**
 * KVKK uyumlu doküman yönetimi.
 *
 * - Yükleme: `local` (private) diske kaydeder, public URL üretmez.
 * - İndirme: signed olmayan route ama auth + office isolation check'le.
 *   Her indirme `activity_log`'a "viewed" event'i olarak yazılır.
 * - Silme: SoftDelete → kayıt activity_log'da kalır.
 */
class DocumentController extends Controller
{
    /**
     * Polimorfik üst kaynak çöz.
     */
    protected function resolveOwner(string $type, int $id): \Illuminate\Database\Eloquent\Model
    {
        $class = match ($type) {
            'lead'    => Lead::class,
            'deal'    => Deal::class,
            'contact' => Contact::class,
            'listing' => Listing::class,
            default   => abort(404, 'Bilinmeyen kaynak tipi.'),
        };

        $model = $class::findOrFail($id);
        $this->authorizeAccess($model);
        return $model;
    }

    /**
     * Office isolation — kullanıcı sadece kendi ofisinin kaynaklarına erişebilir.
     */
    protected function authorizeAccess($model): void
    {
        $myOffice = auth()->user()?->office_id;
        if (!$myOffice) return;          // single-office veya superadmin durumu
        if (!isset($model->office_id)) return;
        if ((int) $model->office_id !== (int) $myOffice) {
            abort(403, 'Bu kayda erişim yetkiniz yok.');
        }
    }

    public function index(Request $request, string $type, int $id)
    {
        $owner = $this->resolveOwner($type, $id);

        $documents = Document::query()
            ->where('documentable_type', get_class($owner))
            ->where('documentable_id', $owner->id)
            ->with('uploader:id,name')
            ->latest()
            ->get();

        return response()->json([
            'documents' => $documents->map(fn ($d) => [
                'id'             => $d->id,
                'title'          => $d->title,
                'original_name'  => $d->original_name,
                'category'       => $d->category,
                'category_label' => $d->category_label,
                'mime_type'      => $d->mime_type,
                'size'           => $d->formatted_size,
                'uploaded_by'    => $d->uploader?->name,
                'uploaded_at'    => $d->created_at?->diffForHumans(),
                'is_confidential'=> $d->is_confidential,
                'download_url'   => route('admin.documents.download', $d),
            ]),
        ]);
    }

    public function store(Request $request, string $type, int $id)
    {
        $owner = $this->resolveOwner($type, $id);

        $validated = $request->validate([
            'file'            => 'required|file|max:20480',                   // 20 MB
            'title'           => 'nullable|string|max:255',
            'category'        => 'nullable|string|in:contract,identity,deed,valuation,photo,other',
            'notes'           => 'nullable|string|max:1000',
            'is_confidential' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $safeName = uniqid('doc_', true) . ($extension ? '.' . $extension : '');

        $path = $file->storeAs("documents/{$type}/{$id}", $safeName, 'local');

        $document = Document::create([
            'documentable_type' => get_class($owner),
            'documentable_id'   => $owner->id,
            'office_id'         => $owner->office_id ?? auth()->user()?->office_id,
            'uploaded_by'       => auth()->id(),
            'title'             => $validated['title'] ?? $originalName,
            'original_name'     => $originalName,
            'file_path'         => $path,
            'mime_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'category'          => $validated['category'] ?? 'other',
            'notes'             => $validated['notes'] ?? null,
            'is_confidential'   => $request->boolean('is_confidential', true),
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Doküman yüklendi.',
            'document' => [
                'id'           => $document->id,
                'title'        => $document->title,
                'download_url' => route('admin.documents.download', $document),
            ],
        ]);
    }

    public function download(Document $document)
    {
        $this->authorizeAccess($document->documentable);

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'Dosya bulunamadı.');
        }

        // KVKK için indirme/görüntüleme audit log'a yazılır
        activity()
            ->causedBy(auth()->user())
            ->performedOn($document)
            ->withProperties([
                'documentable_type' => $document->documentable_type,
                'documentable_id'   => $document->documentable_id,
                'ip'                => request()->ip(),
            ])
            ->event('downloaded')
            ->log("Doküman indirildi: {$document->title}");

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name ?: $document->title
        );
    }

    public function destroy(Document $document)
    {
        $this->authorizeAccess($document->documentable);

        // SoftDelete → audit trail kayıttaki kalsın
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Doküman silindi (yedeği audit log\'da saklanıyor).',
        ]);
    }
}
