<?php

it('can fetch an image', function () {
    $url = 'https://fastly.picsum.photos/id/1001/200/300.jpg?hmac=nQhEVl6C7qyfiRmcIe41BohR4WBcN1yhONnlCJryahU';
    $this->get(route('lt.filepond.load', ['source' => $url]))
        ->assertSuccessful()
        ->assertHeader('Content-Type')
        ->assertHeader('Content-Length')
        ->assertHeader('Content-Disposition');
});

it('can\'t fetch an image due wrong url', function () {
    $url = 'https://foo.bar';
    $this->get(route('lt.filepond.load', ['source' => $url]))
        ->assertNotFound();
});
