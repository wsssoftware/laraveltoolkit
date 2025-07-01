<?php

namespace Laraveltoolkit\Tests\StoredAssets;

use Illuminate\Filesystem\Filesystem;

class MoveDirectoryFilesystemTest extends Filesystem
{
    protected $copy = true;

    protected $deleteDirectory = true;

    public function failOnCopy(): self
    {
        $this->copy = false;

        return $this;
    }

    public function failOnDeleteDirectory(): self
    {
        $this->deleteDirectory = false;

        return $this;
    }

    public function copy($path, $target): bool
    {
        return $this->copy;
    }

    public function deleteDirectory($directory, $preserve = false)
    {
        return $this->deleteDirectory;
    }

    public function files($directory, $hidden = false)
    {
        return ['foo'];
    }
}
