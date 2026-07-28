<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

it('can fetch an image', function () {
    $url = 'https://example.test/image.jpg';
    $image = UploadedFile::fake()->image('image.jpg');

    Http::fake([$url => Http::response($image->getContent())]);

    $this->get(route('lt.filepond.load', ['source' => $url]))
        ->assertSuccessful()
        ->assertHeader('Content-Type')
        ->assertHeader('Content-Length')
        ->assertHeader('Content-Disposition');
});

it('can\'t fetch an image due wrong url', function () {
    Http::fake(Http::failedConnection());

    $url = 'https://invalid.example.test';
    $this->get(route('lt.filepond.load', ['source' => $url]))
        ->assertNotFound();
});
