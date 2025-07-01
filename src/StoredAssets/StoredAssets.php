<?php

namespace Laraveltoolkit\StoredAssets;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @see \Laraveltoolkit\Facades\StoredAssets
 */
class StoredAssets
{
    public function basePath(): string
    {
        return config('laraveltoolkit.stored_assets.path');
    }

    public function clearTrashBin(string $disk): int
    {
        $disk = Storage::disk($disk);
        $uuids = collect($disk->directories($this->trashBinPath()))
            ->map(fn (string $path) => substr($path, -36, 36))
            ->filter(fn (string $uuid) => Str::isUuid($uuid));
        if ($uuids->isEmpty()) {
            return 0;
        }
        $this->modelQuery()->whereIn('id', $uuids)->delete();

        return $disk->deleteDirectory($this->trashBinPath()) ? $uuids->count() : 0;
    }

    public function defaultDisk(): string
    {
        return config('laraveltoolkit.stored_assets.disk');
    }

    public function defaultFilenameStoreType(): FilenameStoreType
    {
        $value = config('laraveltoolkit.stored_assets.filename_store_type');

        return $value instanceof FilenameStoreType ? $value : FilenameStoreType::from($value);
    }

    public function deleteFromTrashBin(string $disk, string $uuid): bool
    {
        $disk = Storage::disk($disk);

        $path = collect($disk->directories($this->trashBinPath()))
            ->filter(fn (string $path) => str_ends_with($path, $uuid))
            ->first();
        if (empty($path)) {
            return false;
        }
        $this->modelQuery()->where('id', $uuid)->delete();

        return $disk->deleteDirectory($path);
    }

    public function isValidUuidAsset(string $uuid): bool
    {
        if (! Str::isUuid($uuid)) {
            return false;
        }

        return $this->modelQuery()->where('id', $uuid)->exists();
    }

    public function newModel(array $attributes = []): StoredAssetModel
    {
        return $this->modelFQN()::newModelInstance($attributes);
    }

    /**
     * @return class-string<\Laraveltoolkit\StoredAssets\StoredAssetModel>
     */
    public function modelFQN(): string
    {
        return config('laraveltoolkit.stored_assets.model');
    }

    public function modelQuery(): Builder
    {
        return $this->modelFQN()::query();
    }

    protected function moveDirectory($disk, string $from, string $to): bool
    {
        $disk->deleteDirectory($to);
        $trimmedFrom = str($from)->trim('/')->trim('\\')->toString();
        $trimmedTo = str($to)->trim('/')->trim('\\')->toString();
        $toCopy = collect($disk->files($from, true))
            ->mapWithKeys(
                fn (string $path) => [
                    $path => str($path)
                        ->trim('/')
                        ->trim('\\')
                        ->replace($trimmedFrom, $trimmedTo)
                        ->toString(),
                ]
            );
        foreach ($toCopy as $copyFrom => $copyTo) {
            if (! $disk->copy($copyFrom, $copyTo)) {
                Log::warning(
                    sprintf('Failed to create a copy from "%s" to  "%s"', $from, $to)
                );

                return false;
            }
        }
        if (! $disk->deleteDirectory($from)) {
            Log::warning(
                sprintf('Failed to delete original directory "%s" after creating a copy to "%s".', $from, $to)
            );

            return false;
        }

        return true;
    }

    public function moveToTrashBin(string $disk, string $uuid): bool
    {
        $disk = Storage::disk($disk);
        $originalPath = $this->path($uuid);
        $trashBinUuidFolderName = sprintf('%s-%s', $this->trashBinDeadlineTimestamp(), $uuid);
        if (! $disk->exists($originalPath)) {
            return false;
        }

        return $this->moveDirectory($disk, $originalPath, $this->trashBinPath($trashBinUuidFolderName));
    }

    public function path(string $uuid, ?string $path = null): string
    {
        $part1 = substr($uuid, 0, $this->subdirectoryChars());
        $part2 = substr($uuid, $this->subdirectoryChars() * -1, $this->subdirectoryChars());

        return str($this->basePath())
            ->append(DIRECTORY_SEPARATOR)
            ->append($part1)
            ->append(DIRECTORY_SEPARATOR)
            ->append($part2)
            ->append(DIRECTORY_SEPARATOR)
            ->append($uuid)
            ->append(DIRECTORY_SEPARATOR)
            ->append($path ?? '')
            ->deduplicate(DIRECTORY_SEPARATOR);
    }

    public function restoreFromTrashBin(string $disk, string $uuid): bool
    {
        $disk = Storage::disk($disk);

        $path = collect($disk->directories($this->trashBinPath()))
            ->filter(fn (string $path) => str_ends_with($path, $uuid))
            ->first();
        if (empty($path)) {
            return false;
        }

        return $this->moveDirectory($disk, $path, $this->path($uuid));
    }

    public function subdirectoryChars(): int
    {
        return config('laraveltoolkit.stored_assets.subdirectory_chars');
    }

    public function trashBinDeadlineTimestamp(?Carbon $from = null): int
    {
        $increment = intval(config('laraveltoolkit.stored_assets.trash_bin.deadline'));

        return ($from ?? now())->addMinutes($increment)->startOfHour()->getTimestamp();
    }

    public function trashBinPath(?string $path = null): string
    {
        return str($this->basePath())
            ->append(DIRECTORY_SEPARATOR)
            ->append(config('laraveltoolkit.stored_assets.trash_bin.folder'))
            ->append(DIRECTORY_SEPARATOR)
            ->append($path ?? '')
            ->append(DIRECTORY_SEPARATOR)
            ->deduplicate(DIRECTORY_SEPARATOR);
    }
}
