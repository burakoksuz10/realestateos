<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\CRM\Models\Lead;
use Modules\Core\Models\Office;

/**
 * DocumentController store — MIME whitelist'i, yürütülebilir dosya
 * yüklenmesini engellemeli.
 */

beforeEach(function () {
    seedRoles();
    Storage::fake('local');

    $this->office = Office::factory()->create();
    $this->agent  = User::factory()->create(['office_id' => $this->office->id]);
    $this->agent->assignRole('agent');

    $this->lead = Lead::factory()->create(['office_id' => $this->office->id]);
});

it('rejects a php upload', function () {
    $file = UploadedFile::fake()->createWithContent('shell.php', '<?php phpinfo(); ?>');

    $response = $this->actingAs($this->agent)
        ->post(route('admin.documents.store', ['type' => 'lead', 'id' => $this->lead->id]), [
            'file'  => $file,
            'title' => 'test',
        ]);

    $response->assertSessionHasErrors('file');
});

it('rejects an exe upload', function () {
    $file = UploadedFile::fake()->create('malware.exe', 10);

    $response = $this->actingAs($this->agent)
        ->post(route('admin.documents.store', ['type' => 'lead', 'id' => $this->lead->id]), [
            'file' => $file,
        ]);

    $response->assertSessionHasErrors('file');
});

it('accepts a pdf upload', function () {
    $file = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');

    $response = $this->actingAs($this->agent)
        ->post(route('admin.documents.store', ['type' => 'lead', 'id' => $this->lead->id]), [
            'file' => $file,
        ]);

    $response->assertSessionDoesntHaveErrors('file');
});

it('accepts a png image upload', function () {
    $file = UploadedFile::fake()->image('photo.png');

    $response = $this->actingAs($this->agent)
        ->post(route('admin.documents.store', ['type' => 'lead', 'id' => $this->lead->id]), [
            'file' => $file,
        ]);

    $response->assertSessionDoesntHaveErrors('file');
});
