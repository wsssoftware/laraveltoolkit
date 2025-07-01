<?php

use Illuminate\Http\UploadedFile;
use Laraveltoolkit\Facades\Filepond;

it('can process a normal file', function () {
    $response = $this->post(route('lt.filepond.process'), ['filepond' => UploadedFile::fake()->image('image.jpg')]);

    Filepond::disk()->assertExists(Filepond::path($response->content(), 'image.jpg'));
    $response->assertSuccessful();
    expect($response->content())->toBeUuid();
});

it('can process a chunk file', function () {
    $chunks = [
        str_repeat('a', 50_000),
        str_repeat('b', 50_000),
        str_repeat('c', 50_000),
    ];

    \Illuminate\Support\Facades\Storage::fake(Filepond::diskName());

    $response = $this->post(route('lt.filepond.process'), headers: ['upload_length' => 150_000]);
    $response->assertSuccessful();
    $id = $response->content();
    expect($id)->toBeUuid();

    foreach ($chunks as $index => $chunk) {
        $response = $this
            ->call(
                'PATCH',
                route('lt.filepond.process_chunk', ['id' => $id]),
                server: [
                    'HTTP_UPLOAD_OFFSET' => $index * 50_000, 'HTTP_UPLOAD_LENGTH' => 150_000,
                    'HTTP_UPLOAD_NAME' => 'file.txt',
                ],
                content: $chunk
            );

        $response->assertSuccessful()
            ->assertNoContent();
    }
    expect(Filepond::disk()->path(Filepond::path($id, 'file.txt')))
        ->toBeFile()
        ->and(Filepond::disk()->checksum(Filepond::path($id, 'file.txt')))
        ->toEqual(md5(implode('', $chunks)));
});
