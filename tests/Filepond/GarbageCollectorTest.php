<?php

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Str;
use Laraveltoolkit\Facades\Filepond;

it('test the garbage collector', function () {
    config()->set('laraveltoolkit.filepond.garbage_collector.probability', 1);
    config()->set('laraveltoolkit.filepond.garbage_collector.upload_life', 0);
    config()->set('laraveltoolkit.filepond.garbage_collector.maximum_interactions', 1);
    /** @var FilesystemAdapter $disk */
    $disk = Storage::fake(Filepond::diskName());
    $id = Str::uuid()->toString();
    $path = Filepond::path($id, 'foo.bar');
    $disk->put($path, 'test');
    $this->travel(1)->second();

    Filepond::garbageCollector();

    $disk->assertMissing($path);
});
