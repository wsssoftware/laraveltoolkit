<?php

namespace Laraveltoolkit\Filepond;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Laraveltoolkit\Facades\Filepond;

class MergeChunkFile
{
    protected Filesystem|FilesystemAdapter $disk;

    protected mixed $file;

    protected string $outputPath;

    public function __construct(
        protected string $id,
        string $outputFilename
    ) {
        $this->disk = Filepond::disk();
        $this->outputPath = Filepond::path($this->id, $outputFilename);
        $this->disk->delete($this->outputPath);
        $this->file = tmpfile();
        abort_if($this->file === false, Abortable::make('Could not open file'));
    }

    public function __invoke(): void
    {
        $chunks = Filepond::chunks($this->id);
        $processedChunks = $chunks->reduce(fn (int $carry, string $path) => $this->mergeChunk($carry, $path), 0);
        abort_if($processedChunks !== $chunks->count(), Abortable::make('Something went wrong'));
        rewind($this->file);
        $saved = Filepond::disk()->put($this->outputPath, $this->file);
        abort_if(! $saved, Abortable::make('Something went wrong while writing file'));
        fclose($this->file);
    }

    protected function mergeChunk(int $carry, string $path): int
    {
        fwrite($this->file, Filepond::disk()->get($path));

        return $carry + 1;
    }
}
