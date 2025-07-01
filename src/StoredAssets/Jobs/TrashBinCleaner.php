<?php

namespace Laraveltoolkit\StoredAssets\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laraveltoolkit\Facades\StoredAssets;

class TrashBinCleaner implements ShouldBeUnique, ShouldQueue
{
    use HasDisk, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $disk,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = $this->disk($this->disk);
        /** @var \Illuminate\Support\Collection $directories */
        $directories = collect($disk->directories(StoredAssets::trashBinPath()))
            ->map(fn(string $path) => str($path)->trim('/')->afterLast('/')->toString())
            ->map(fn(string $folder) => [
                'deadline' => str($folder)->before('-')->toInteger(),
                'uuid' => str($folder)->after('-')->toString(),
            ])
            ->filter(fn(array $data) => Str::isUuid($data['uuid']) && is_numeric($data['deadline']));

        $now = now()->getTimestamp();
        $readyToDelete = $directories->filter(fn(array $data) => intval($data['deadline'] <= $now))
            ->map(fn(array $data) => $data['uuid']);

        $readyToDelete->each(fn(string $uuid) => StoredAssets::deleteFromTrashBin($this->disk, $uuid));

        $directoriesCount = $directories->count();
        $readyToDeleteCount = $readyToDelete->count();
        Log::info(sprintf(
            'In the trash bin on the "%s" disk, %s found, of which %s deleted because %s had reached.',
            $this->disk,
            $directoriesCount.' '.($directoriesCount === 1 ? 'item was' : 'items were'),
            $readyToDeleteCount.' '.($readyToDeleteCount === 1 ? 'item was' : 'items were'),
            $readyToDeleteCount === 1 ? 'its deadline' : 'their deadlines',
        ));
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return "$this->disk";
    }
}
