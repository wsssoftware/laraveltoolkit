<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

it('can fetch an image', function () {
    $url = 'https://example.test/image.jpg';
    $image = UploadedFile::fake()->image('image.jpg');

    Http::fake([$url => Http::response($image->getContent())]);

    $this->get(route('lt.filepond.fetch', ['url' => $url]))
        ->assertSuccessful()
        ->assertHeader('Access-Control-Expose-Headers', 'Content-Disposition, Content-Length, X-Content-Transfer-Id')
        ->assertHeader('Content-Type')
        ->assertHeader('Content-Length')
        ->assertHeader('Content-Disposition')
        ->assertHeader('X-Content-Transfer-Id');
});

it('can\'t fetch an image due wrong url', function () {
    Http::fake(Http::failedConnection());

    $url = 'https://invalid.example.test';
    $this->get(route('lt.filepond.fetch', ['url' => $url]))
        ->assertNotFound();
});
