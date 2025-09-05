<?php

namespace Laraveltoolkit\StoredAssets\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laraveltoolkit\Facades\StoredAssets;

use function Pest\Laravel\get;

class GarbageCollector implements ShouldBeUnique, ShouldQueue
{
    use HasDisk, Queueable;

    protected const string MOVED_COUNT_CACHE_KEY = 'lt_stored_assets_garbage_collector_moved_items';

    protected const string CLEANED_COUNT_CACHE_KEY = 'lt_stored_assets_garbage_collector_cleaned_items';

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $disk,
        protected string $path,
    ) {
        $this->path = str($this->path)->trim('/')->toString();
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = $this->disk($this->disk);
        $subDirectories = collect($disk->directories($this->path));
        if ($subDirectories->isEmpty()) {
            $disk->deleteDirectory($this->path);

            return;
        }

        foreach ($subDirectories as $subDirectory) {
            $idSuffix = str($subDirectory)
                ->substrReplace('', 0, strlen(StoredAssets::basePath()))
                ->remove(['/', '\\'])
                ->toString();
            /** @var Collection $assets */
            $assets = collect($disk->directories($subDirectory))
                ->map(fn (string $path) => str($path)->trim('/')->afterLast('/')->toString())
                ->filter(fn (string $uuid) => Str::isUuid($uuid))
                ->mapWithKeys(fn (string $uuid) => [$uuid => true]);
            if ($assets->isEmpty()) {
                $disk->deleteDirectory($subDirectory);

                continue;
            }

            StoredAssets::modelQuery()
                ->selectRaw("CONCAT(field, '::', model) as field_model")
                ->where('id_suffix', $idSuffix)
                ->groupBy('field_model')
                ->get()
                ->map(fn (Model $model) => $model->getAttribute('field_model'))
                ->each(fn (string $fieldModel) => $this->inspectGroup($fieldModel, $assets));

            $result = $assets
                ->mapWithKeys(fn (bool $value, string $uuid) => [
                    $uuid => StoredAssets::moveToTrashBin($this->disk, $uuid),
                ]);
            self::incrementMovedCount($result->filter(fn (bool $success) => $success)->count());
        }
    }

    protected function inspectGroup(string $fieldModel, Collection $assets): void
    {
        [$field, $model] = explode('::', $fieldModel);
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
        $foundIds = $model::query()
            ->withoutGlobalScopes()
            ->select([$field])
            ->whereIn($field, $assets->keys())
            ->get()
            ->pluck("{$field}_uuid");
        $assets->forget($foundIds);
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return "$this->disk::$this->path";
    }

    public static function incrementMovedCount(int $by = 1): void
    {
        $oldValue = static::getMovedCount();
        Cache::set(self::MOVED_COUNT_CACHE_KEY, $oldValue + $by);
    }

    public static function incrementCleanedCount(int $by = 1): void
    {
        $oldValue = static::getCleanedCount();
        Cache::set(self::CLEANED_COUNT_CACHE_KEY, $oldValue + $by);
    }

    public static function getMovedCount(): int
    {
        return Cache::get(self::MOVED_COUNT_CACHE_KEY, fn () => 0);
    }

    public static function getCleanedCount(): int
    {
        return Cache::get(self::CLEANED_COUNT_CACHE_KEY, fn () => 0);
    }

    public static function clearCounts(): void
    {
        Cache::forget(self::MOVED_COUNT_CACHE_KEY);
        Cache::forget(self::CLEANED_COUNT_CACHE_KEY);
    }
}
