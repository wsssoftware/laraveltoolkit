<?php

namespace Laraveltoolkit\Macros;

use Illuminate\Http\Request;
use Laraveltoolkit\Filepond\UploadedFile;

class RequestMacro
{
    public function __invoke(): void
    {
        $this->filepondMacros();
    }

    public function filepondMacros(): void
    {
        Request::macro('filepond', function (string $input): ?UploadedFile {
            return UploadedFile::fromId($this->input($input));
        });
        Request::macro('mergeFilepond', function (string $input): void {
            $this->merge([
                $input => $this->filepond($input),
            ]);
        });

    }
}
