<?php

namespace Laraveltoolkit\Filepond;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Lottery;
use Illuminate\Support\Str;

class Filepond
{
    public function chunks(string $id): Collection
    {
        return collect($this->disk()->files($this->path($id)))
            ->filter(fn (string $name) => str_ends_with($name, $this->chunkPostfix($id)))
            ->sort(SORT_NATURAL);
    }

    public function chunkPostfix(string $id): string
    {
        return str($id)->before('-')->append('.chunk')->toString();
    }

    public function clearChunk(string $id): void
    {
        $this->chunks($id)->each(fn ($chunk) => $this->disk()->delete($chunk));
    }

    public function clearUploadFolder(): void
    {
        foreach ($this->disk()->directories($this->rootPath()) as $directory) {
            $this->disk()->deleteDirectory($directory);
        }
    }

    public function currentChunksSize(string $id): int
    {
        return $this->chunks($id)
            ->sum(fn ($chunk) => $this->disk()->size($chunk));
    }

    public function delete(string $id): bool
    {
        return $this->disk()->deleteDirectory($this->path($id));
    }

    public function disk(): FilesystemAdapter|Filesystem
    {
        return Storage::disk($this->diskName());
    }

    public function diskName(): string
    {
        return config('laraveltoolkit.filepond.disk');
    }

    public function garbageCollector(): void
    {
        Lottery::odds(floatval(config('laraveltoolkit.filepond.garbage_collector.probability')))
            ->winner(fn () => (new GarbageCollector)())
            ->choose();
    }

    public function generateId(): string
    {
        return Str::uuid()->toString();
    }

    public function path(string $id, ?string $path = null): string
    {
        return sprintf(
            '%s%s%s%s%s',
            $this->rootPath(),
            DIRECTORY_SEPARATOR,
            $id,
            DIRECTORY_SEPARATOR,
            $path ?? '',
        );
    }

    public function rootPath(): string
    {
        return config('laraveltoolkit.filepond.root_path');
    }
}
