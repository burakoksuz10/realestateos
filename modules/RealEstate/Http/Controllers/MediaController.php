<?php

namespace Modules\RealEstate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Listing;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function upload(Request $request, Listing $listing)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,webp,mp4,webm,pdf|max:51200',
            'collection' => 'nullable|string|in:photos,videos,floor_plans,documents,virtual_tour',
        ]);

        $collection = $request->input('collection', 'photos');

        $media = $listing->addMediaFromRequest('file')
            ->usingFileName(time() . '_' . $request->file('file')->getClientOriginalName())
            ->toMediaCollection($collection);

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                'name' => $media->file_name,
                'size' => $media->size,
                'collection' => $media->collection_name,
            ],
        ]);
    }

    public function destroy(Listing $listing, Media $media)
    {
        if ((int) $media->model_id !== $listing->id) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }

        $media->delete();

        return response()->json(['success' => true, 'message' => 'Medya silindi.']);
    }

    public function reorder(Request $request, Listing $listing)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        foreach ($request->ids as $order => $mediaId) {
            Media::where('id', $mediaId)
                ->where('model_id', $listing->id)
                ->update(['order_column' => $order + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Sıralama güncellendi.']);
    }
}
