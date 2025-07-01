<?php

namespace Laraveltoolkit\Filepond;

use Illuminate\Support\Facades\Log;
use Laraveltoolkit\Facades\Filepond;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoTmpDirFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;
use Symfony\Component\HttpFoundation\File\File;

class UploadedFile extends \Illuminate\Http\UploadedFile
{
    private bool $forceValidOnTest = false;

    public static function fromId(?string $id): ?self
    {
        if (empty($id)) {
            return null;
        }
        if (!str($id)->isUuid()) {
            Log::warning('Tried to get an uploaded file with a non-uuid identifier.');

            return null;
        }
        $disk = Filepond::disk();
        $files = $disk->files(Filepond::path($id));
        if (count($files) !== 1) {
            return null;
        }
        defer(function () use ($id) {
            if (session()->missing('errors')) {
                Filepond::delete($id);
            }
        });

        return new self(
            $disk->path($files[0]),
            basename($files[0]),
            $disk->mimeType($files[0]),
        );
    }

    private function getTest(): bool
    {
        $reflection = new \ReflectionClass(\Symfony\Component\HttpFoundation\File\UploadedFile::class);

        return $reflection->getProperty('test')->getValue($this);
    }

    public function isValid(): bool
    {
        $isOk = $this->getError() === \UPLOAD_ERR_OK;

        return $this->getTest() || $this->forceValidOnTest ? $isOk : $isOk && file_exists($this->getPathname());
    }

    public function move(string $directory, ?string $name = null): File
    {
        if ($this->isValid()) {
            if ($this->getTest()) {
                return parent::move($directory, $name);
            }

            $target = $this->getTargetFile($directory, $name);

            set_error_handler(function ($type, $msg) use (&$error) {
                $error = $msg;
            });
            try {
                $moved = rename($this->getPathname(), $target);
            } finally {
                restore_error_handler();
            }
            if (!$moved) {
                throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s).', $this->getPathname(),
                    $target, strip_tags($error)));
            }

            @chmod($target, 0666 & ~umask());

            return $target;
        }

        switch ($this->getError()) {
            case \UPLOAD_ERR_INI_SIZE:
                throw new IniSizeFileException($this->getErrorMessage());
            case \UPLOAD_ERR_FORM_SIZE:
                throw new FormSizeFileException($this->getErrorMessage());
            case \UPLOAD_ERR_PARTIAL:
                throw new PartialFileException($this->getErrorMessage());
            case \UPLOAD_ERR_NO_FILE:
                throw new NoFileException($this->getErrorMessage());
            case \UPLOAD_ERR_CANT_WRITE:
                throw new CannotWriteFileException($this->getErrorMessage());
            case \UPLOAD_ERR_NO_TMP_DIR:
                throw new NoTmpDirFileException($this->getErrorMessage());
            case \UPLOAD_ERR_EXTENSION:
                throw new ExtensionFileException($this->getErrorMessage());
        }

        throw new FileException($this->getErrorMessage());
    }
}
