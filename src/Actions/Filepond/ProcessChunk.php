<?php

namespace Laraveltoolkit\Actions\Filepond;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laraveltoolkit\Facades\Filepond;
use Laraveltoolkit\Filepond\Abortable;
use Laraveltoolkit\Filepond\MergeChunkFile;

class ProcessChunk
{
    protected Filesystem|FilesystemAdapter $disk;

    protected ?string $id;

    protected Request $request;

    public function __invoke(Request $request): Response
    {
        $this->request = $request;
        $this->disk = Filepond::disk();
        $this->id = $request->input('id');
        abort_if(empty($this->id), Abortable::make('No upload id provided', 404));
        abort_if($this->disk->directoryMissing(Filepond::path($this->id)), Abortable::make('Invalid upload', 404));

        $offset = $request->server('HTTP_UPLOAD_OFFSET');
        abort_if(
            !is_numeric($offset) || !is_numeric($request->server('HTTP_UPLOAD_LENGTH')),
            Abortable::make('Invalid chunk length or offset')
        );

        $this->disk->put(
            Filepond::path($this->id, $offset.'.'.Filepond::chunkPostfix($this->id)), $request->getContent(),
            ['mimetype' => 'application/octet-stream']
        );

        return response(null, $this->wasPersisted(), headers: ['Content-Type' => 'text/plain']);
    }

    private function wasPersisted(): int
    {
        $size = Filepond::currentChunksSize($this->id);
        $wantedSize = (int) $this->request->headers->get('upload-length', 0);
        if ($size < $wantedSize) {
            return 204;
        }
        $outputFilename = $this->request->headers->get('upload-name');
        $isInvalid = empty($outputFilename) || !is_string($outputFilename);
        abort_if($isInvalid, Abortable::make('No file name provided'));
        (new MergeChunkFile($this->id, $outputFilename))();
        defer(function () {
            Filepond::clearChunk($this->id);
            Filepond::garbageCollector();
        });
        $path = Filepond::path($this->id, $outputFilename);
        $size = $this->disk->size($path);

        return $this->disk->exists($path) && $size === $wantedSize ? 204 : 500;
    }
}
