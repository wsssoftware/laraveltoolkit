<?php

namespace Laraveltoolkit\StoredAssets;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

readonly class Asset implements Arrayable
{
    public function __construct(
        public string $assetsUuid,
        public string $disk,
        public string $key,
        public string $pathname,
        public string $extension,
        public string $mimeType,
        public int $size,
    ) {
    }

    public function disk(): Filesystem|FilesystemAdapter
    {
        return Storage::disk($this->disk);
    }

    public function get(): string
    {
        return $this->disk()->get($this->pathname);
    }

    public function readStream()
    {
        return $this->disk()->readStream($this->pathname);
    }

    public function url(): string
    {
        return $this->disk()->url($this->pathname);
    }

    public function toArray(): array
    {
        return [
            'assets_uuid' => $this->assetsUuid,
            'disk' => $this->disk,
            'key' => $this->key,
            'pathname' => $this->pathname,
            'extension' => $this->extension,
            'mime_type' => $this->mimeType,
            'size' => $this->size,
        ];
    }

    public static function fromDatabase(string $uuid, array $data): self
    {
        return new self(
            $uuid,
            Arr::get($data, 'd'),
            Arr::get($data, 'k'),
            Arr::get($data, 'p'),
            Arr::get($data, 'e'),
            Arr::get($data, 'm'),
            Arr::get($data, 's'),
        );
    }

    public function toDatabase(): array
    {
        return [
            'd' => $this->disk,
            'k' => $this->key,
            'p' => $this->pathname,
            'e' => $this->extension,
            'm' => $this->mimeType,
            's' => $this->size,
        ];
    }
}
