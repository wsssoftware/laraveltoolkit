<?php

namespace Laraveltoolkit\Actions\Filepond;

use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Laraveltoolkit\Facades\Filepond;
use Laraveltoolkit\Filepond\Abortable;

class Load
{
    public function __invoke(Request $request): Response
    {
        $source = $request->input('source');
        abort_if(empty($source), Abortable::make('No fetch url provided', 404));

        return $this->fromUrl($source);
    }

    protected function fromUrl(string $url): Response
    {
        try {
            $response = Http::get($url);
        } catch (Exception) {
            abort(Abortable::make('Fetch url failed', 404));
        }
        $id = Filepond::generateId();
        $body = $response->getBody();

        $tmpPath = Filepond::path($id, str($id)->before('-')->append('.downloaded.tmp'));
        Filepond::disk()->put($tmpPath, $body);
        $file = new File(Filepond::disk()->path($tmpPath));
        $filename = $this->filename($url, $file);
        $path = Filepond::path($id, $filename);
        Filepond::disk()->move($tmpPath, $path);
        $file = new File(Filepond::disk()->path($path));

        return response($file->getContent(), 200, [
            'Content-Type' => $file->getMimeType(),
            'Content-Length' => $file->getSize(),
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    protected function filename(string $url, File $file): string
    {
        $pathParts = pathinfo($url);
        $filename = ! empty($pathParts['filename']) ? $pathParts['filename'] : 'download';

        return "$filename.{$file->guessExtension()}";
    }
}
