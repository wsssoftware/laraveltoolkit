<?php

namespace Laraveltoolkit\StoredAssets\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laraveltoolkit\Facades\StoredAssets;
use Laraveltoolkit\StoredAssets\StoredAssetModel;

class GarbageCollector implements ShouldBeUnique, ShouldQueue
{
    use HasDisk, Queueable;

    protected const string MOVED_COUNT_CACHE_KEY = 'lt_stored_assets_garbage_collector_moved_items';

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
            $collection = collect($disk->directories($subDirectory))
                ->map(fn(string $path) => str($path)->trim('/')->afterLast('/'))
                ->filter(fn(string $uuid) => Str::isUuid($uuid));
            if ($collection->isEmpty()) {
                $disk->deleteDirectory($subDirectory);

                continue;
            }
            do {
                $inspectedUuids = $collection->pop(200)
                    ->mapWithKeys(fn(string $uuid) => [$uuid => $uuid]);
                StoredAssets::modelQuery()
                    ->select('model', 'field')
                    ->whereIn('id', $inspectedUuids)
                    ->get()
                    ->groupBy(fn(StoredAssetModel $asset) => "$asset->field::$asset->model")
                    ->each(fn(Collection $group) => $this->inspectGroup($group, $inspectedUuids));

                /** @var Collection $result */
                $result = $inspectedUuids->mapWithKeys(fn(string $uuid
                ) => [$uuid => StoredAssets::moveToTrashBin($this->disk, $uuid)]);
                self::incrementCount($result->filter(fn(bool $success) => $success)->count());
            } while ($collection->count() > 0);
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, StoredAssetModel>  $group
     */
    protected function inspectGroup(Collection $group, Collection $inspectedUuids): void
    {
        $first = $group->first();
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
        $model = $first->model;
        $foundIds = $model::query()
            ->withoutGlobalScopes()
            ->select([$first->field])
            ->whereIn($first->field, $inspectedUuids)
            ->get()
            ->pluck("{$first->field}_uuid");
        $inspectedUuids->forget($foundIds);
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return "$this->disk::$this->path";
    }

    public static function incrementCount(int $by = 1): void
    {
        $oldValue = static::getCount();
        Cache::set(self::MOVED_COUNT_CACHE_KEY, $oldValue + $by);
    }

    public static function getCount(): int
    {
        return Cache::get(self::MOVED_COUNT_CACHE_KEY, fn() => 0);
    }

    public static function clearCount(): void
    {
        Cache::forget(self::MOVED_COUNT_CACHE_KEY);
    }
}
