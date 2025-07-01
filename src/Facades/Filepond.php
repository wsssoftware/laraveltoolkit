<?php

namespace Laraveltoolkit\Facades;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection chunks(string $id)
 * @method static string chunkPostfix(string $id)
 * @method static void clearChunk(string $id)
 * @method static void clearUploadFolder()
 * @method static int currentChunksSize(string $id)
 * @method static FilesystemAdapter|Filesystem disk()
 * @method static bool delete(string $id)
 * @method static string diskName()
 * @method static void garbageCollector()
 * @method static string generateId()
 * @method static string path(string $id, string $path = null)
 * @method static string rootPath()
 *
 * @see \Laraveltoolkit\Filepond\Filepond
 */
class Filepond extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Laraveltoolkit\Filepond\Filepond::class;
    }
}
