<?php

use Laraveltoolkit\Facades\Filepond;

it('test the garbage collector', function () {
    config()->set('laraveltoolkit.filepond.garbage_collector.probability', 1);
    config()->set('laraveltoolkit.filepond.garbage_collector.upload_life', 0);
    config()->set('laraveltoolkit.filepond.garbage_collector.maximum_interactions', 1);
    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
    $disk = Storage::fake(Filepond::diskName());
    $id = \Illuminate\Support\Str::uuid()->toString();
    $path = Filepond::path($id, 'foo.bar');
    $disk->put($path, 'test');
    sleep(1);

    Filepond::garbageCollector();

    $disk->assertMissing($path);
});
