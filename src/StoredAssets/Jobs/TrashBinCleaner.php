<?php

namespace Laraveltoolkit\StoredAssets\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laraveltoolkit\Facades\StoredAssets;
use Laraveltoolkit\Support\HasTimeoutHandler;

class TrashBinCleaner implements ShouldBeUnique, ShouldQueue
{
    use HasDisk, HasTimeoutHandler, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $disk,
    ) {
        $this->safeTimeout = config('laraveltoolkit.stored_assets.trash_bin_cleaner_timeout', 55);
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle(): void
    {
        $this->startTimeoutHandler();
        $disk = $this->disk($this->disk);
        /** @var \Illuminate\Support\Collection $directories */
        $directories = collect($disk->directories(StoredAssets::trashBinPath()))
            ->map(fn (string $path) => str($path)->trim('/')->afterLast('/')->toString())
            ->map(fn (string $folder) => [
                'deadline' => str($folder)->before('-')->toInteger(),
                'uuid' => str($folder)->after('-')->toString(),
            ])
            ->filter(fn (array $data) => Str::isUuid($data['uuid']) && is_numeric($data['deadline']));

        $now = now()->getTimestamp();
        $readyToDelete = $directories->filter(fn (array $data) => intval($data['deadline'] <= $now))
            ->map(fn (array $data) => $data['uuid']);

        $deleted = 0;
        foreach ($readyToDelete as $uuid) {
            if (StoredAssets::deleteFromTrashBin($this->disk, $uuid)) {
                $deleted++;
            }
            if ($this->itsApproachingTimeout()) {
                break;
            }
        }

        $directoriesCount = $directories->count();
        $availableToDelete = $readyToDelete->count();
        Log::info(sprintf(
            'In the trash bin on the "%s" disk, %s found, of which %s of %s deleted because %s had reached.',
            $this->disk,
            $directoriesCount.' '.($directoriesCount === 1 ? 'item was' : 'items were'),
            $deleted,
            $availableToDelete.' '.($deleted === 1 ? 'item was' : 'items were'),
            $deleted === 1 ? 'its deadline' : 'their deadlines',
        ));
        if ($deleted < $availableToDelete) {
            defer(fn () => self::dispatch($this->disk));
        }
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return "$this->disk";
    }
}
