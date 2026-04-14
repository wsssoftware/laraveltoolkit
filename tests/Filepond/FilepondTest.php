<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laraveltoolkit\Facades\Filepond;
use Laraveltoolkit\Filepond\UploadedFile;

it('can clear all files', function () {
    $disk = Storage::fake(Filepond::diskName());
    $disk->put(Filepond::path(Str::uuid(), 'text1.txt'), '');
    $disk->put(Filepond::path(Str::uuid(), 'text1.txt'), '');
    $disk->put(Filepond::path(Str::uuid(), 'text1.txt'), '');

    expect($disk->directories(Filepond::rootPath()))
        ->toHaveCount(3);
    Filepond::clearUploadFolder();
    expect($disk->directories(Filepond::rootPath()))
        ->toHaveCount(0);

    $id = Str::uuid();
    $disk->put(Filepond::path($id, 'text1.txt'), '');
    expect($disk->directories(Filepond::rootPath()))
        ->toHaveCount(1)
        ->and(Filepond::delete($id))
        ->toBeTrue()
        ->and($disk->directories(Filepond::rootPath()))
        ->toHaveCount(0);
});

it('can send request', function () {
    $id = Str::uuid()->toString();
    $disk = Storage::fake(Filepond::diskName());
    $disk->put(Filepond::path($id, 'text1.txt'), '');
    Route::post('foo-bar', function (Request $request) {
        expect($request->filepond('foo'))
            ->toBeInstanceOf(UploadedFile::class)
            ->and($request->input('foo'))
            ->toBeString()
            ->and($request->mergeFilepond('foo'))
            ->toBeNull()
            ->and($request->input('foo'))
            ->toBeInstanceOf(UploadedFile::class);
    });

    $this->post('foo-bar', ['foo' => $id])
        ->assertSuccessful();
});

it('can do a complete flow', function () {
    $response = $this->post(route('lt.filepond.process'), ['filepond' => UploadedFile::fake()->image('image.jpg')]);

    Filepond::disk()->assertExists(Filepond::path($response->content(), 'image.jpg'));
    $response->assertSuccessful();
    $id = $response->content();
    expect($id)
        ->toBeUuid()
        ->and(Filepond::disk()->path(Filepond::path($id, 'image.jpg')))
        ->toBeFile();

    Route::post('example-route', function (Request $request) {
        $request->mergeFilepond('id');
        $validated = $request->validate(['id' => 'required|image']);
        /** @var UploadedFile $filepond */
        $filepond = $validated['id'];
        $filepond->storeAs('/test', 'foo.jpg');
    });

    $this->post('example-route', ['id' => $id])
        ->assertSuccessful();

    expect(Storage::path('test/foo.jpg'))
        ->toBeFile()
        ->and(Filepond::disk()->path(Filepond::path($id, 'image.jpg')))
        ->not
        ->toBeFile();
});

it('can do a complete flow with fail on validation', function () {
    $response = $this->post(route('lt.filepond.process'), ['filepond' => UploadedFile::fake()->image('image.jpg')]);

    Filepond::disk()->assertExists(Filepond::path($response->content(), 'image.jpg'));
    $response->assertSuccessful();
    $id = $response->content();
    expect($id)
        ->toBeUuid()
        ->and(Filepond::disk()->path(Filepond::path($id, 'image.jpg')))
        ->toBeFile();

    Route::post('example-route', function (Request $request) {
        $request->mergeFilepond('id');
        $validated = $request->validate(['id' => 'required|mimes:zip']);
        /** @var UploadedFile $filepond */
        $filepond = $validated['id'];
        $filepond->storeAs('/test', 'foo.jpg');
    });

    $this->post('example-route', ['id' => $id])
        ->assertRedirect()
        ->assertSessionHasErrors();

    expect(Filepond::disk()->path(Filepond::path($id, 'image.jpg')))
        ->toBeFile();
});
