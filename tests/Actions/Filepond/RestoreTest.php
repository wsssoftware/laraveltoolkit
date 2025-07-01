<?php

use Laraveltoolkit\Facades\Filepond;

it('can restore a file', function () {
    Storage::fake(Filepond::diskName());
    $id = Str::uuid()->toString();
    Filepond::disk()->put(Filepond::path($id, 'foo.zip'), 'content example');

    $response = $this->get(route('lt.filepond.restore', ['id' => $id]));
    $response->assertSuccessful()
        ->assertContent('content example')
        ->assertHeader('Content-Disposition', 'inline; filename="foo.zip"')
        ->assertHeader('Content-Type');
});
