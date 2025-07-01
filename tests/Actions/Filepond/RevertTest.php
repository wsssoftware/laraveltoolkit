<?php

use Laraveltoolkit\Facades\Filepond;

it('can revert an uploaded file', function () {
    $id = Str::uuid()->toString();
    $path = Filepond::path($id, 'foo.bar');
    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
    $disk = \Illuminate\Support\Facades\Storage::fake(Filepond::diskName());
    $disk->put($path, 'foo bar content');

    expect($disk->fileExists($path))
        ->toBeTrue();
    $response = $this->call('DELETE', route('lt.filepond.revert'), content: $id);
    $response
        ->assertSuccessful()
        ->assertNoContent();

    expect($disk->fileExists($path))
        ->toBeFalse();
});
