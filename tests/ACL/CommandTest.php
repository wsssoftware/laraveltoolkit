<?php

it('can create model', function () {
    $path = base_path('app/Models/UserPermission.php');
    if (file_exists($path)) {
        unlink($path);
    }
    $this->artisan('make:acl-model');
    expect($path)
        ->toBeFile();
});
