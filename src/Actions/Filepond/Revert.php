<?php

namespace Laraveltoolkit\Actions\Filepond;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laraveltoolkit\Facades\Filepond;

class Revert
{
    public function __invoke(Request $request): Response
    {
        $path = Filepond::path($request->getContent());
        defer(fn () => $this->delete($path));

        return response()->noContent();
    }

    protected function delete(string $path): void
    {
        $disk = Filepond::disk();
        if ($disk->directoryExists($path)) {
            $disk->deleteDirectory($path);
        }
    }
}
