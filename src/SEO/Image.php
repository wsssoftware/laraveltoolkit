<?php

namespace Laraveltoolkit\SEO;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

readonly class Image
{
    public function __construct(
        public string $disk,
        public string $path,
        public ?string $alt = null,
    ) {
        //
    }

    public function toUrl(): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);
        $lastChange = Cache::remember(
            "seo_image:$this->disk:$this->path",
            604_800,
            fn () => $this->getLastModified($disk)
        );

        return $disk->url($this->path.$lastChange);
    }

    protected function getLastModified(Filesystem|FilesystemAdapter $disk): string
    {
        try {
            return '?'.$disk->lastModified($this->path);
        } catch (Exception) {
            return '';
        }
    }
}
