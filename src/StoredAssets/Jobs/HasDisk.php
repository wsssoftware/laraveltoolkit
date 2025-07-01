<?php

namespace Laraveltoolkit\StoredAssets\Jobs;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

trait HasDisk
{
    protected function disk(string $name): Filesystem|FilesystemAdapter
    {
        return Storage::disk($name);
    }
}
