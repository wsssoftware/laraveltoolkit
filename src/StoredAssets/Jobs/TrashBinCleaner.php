<?php

namespace Laraveltoolkit\StoredAssets\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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
    ) {}

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
                GarbageCollector::incrementCleanedCount();
            }
            if ($this->itsApproachingTimeout()) {
                break;
            }
        }

        $availableToDelete = $readyToDelete->count();
        if ($deleted < $availableToDelete) {
            defer(fn () => self::dispatch($this->disk));
        }
    }

    protected function safeTimeout(): int
    {
        return config('laraveltoolkit.stored_assets.trash_bin_cleaner_timeout', 55);
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return "$this->disk";
    }
}
