<?php

namespace Laraveltoolkit\Filepond;

use Illuminate\Support\Facades\Storage;

class GarbageCollector
{
    public function __invoke()
    {
        $storage = Storage::disk(config('laraveltoolkit.filepond.disk'));
        $uploadLife = intval(config('laraveltoolkit.filepond.garbage_collector.upload_life'));
        $timestampLimit = now()->subSeconds($uploadLife)->timestamp;
        $deleted = 0;
        $maximumInteractions = intval(config('laraveltoolkit.filepond.garbage_collector.maximum_interactions'));
        foreach ($storage->directories(config('laraveltoolkit.filepond.root_path')) as $directory) {
            if ($storage->lastModified($directory) <= $timestampLimit) {
                $storage->deleteDirectory($directory);
                $deleted++;
            }
            if ($deleted >= $maximumInteractions) {
                break;
            }
        }
    }
}
