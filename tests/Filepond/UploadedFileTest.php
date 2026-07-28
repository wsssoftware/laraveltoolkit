<?php

use Laraveltoolkit\Facades\Filepond;
use Laraveltoolkit\Filepond\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoTmpDirFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;
use Symfony\Component\HttpFoundation\File\File;

it('creates, validates and moves uploaded files', function () {
    $disk = Storage::fake(Filepond::diskName());

    expect(UploadedFile::fromId(''))
        ->toBeNull()
        ->and(UploadedFile::fromId(null))
        ->toBeNull()
        ->and(UploadedFile::fromId(Str::uuid()))
        ->toBeNull()
        ->and(UploadedFile::fromId('not a valid uuid'))
        ->toBeNull();

    $id = Str::uuid();
    $disk->put(Filepond::path($id, 'image.png'), 'foo bar');
    $file = UploadedFile::fromId($id);

    expect($file)
        ->toBeInstanceOf(UploadedFile::class)
        ->and($file->isValid())
        ->toBeTrue()
        ->and($file->move($disk->path('tmp/from-id')))
        ->toBeInstanceOf(File::class);

    $id = Str::uuid();
    $path = Filepond::path($id, 'image.png');
    $disk->put($path, 'foo bar');
    $testFile = new UploadedFile($disk->path($path), 'image.png', test: true);

    expect($testFile->isValid())
        ->toBeTrue()
        ->and($testFile->move($disk->path('tmp/test-file')))
        ->toBeInstanceOf(File::class);

    $id = Str::uuid();
    $path = Filepond::path($id, 'image.png');
    $disk->put($path, 'foo bar');
    $unmovableFile = UploadedFile::fromId($id);
    $disk->delete($path);
    new ReflectionClass($unmovableFile)->getProperty('forceValidOnTest')->setValue($unmovableFile, true);

    expect($unmovableFile->isValid())
        ->toBeTrue()
        ->and(fn () => $unmovableFile->move($disk->path('missing/directory')))
        ->toThrow(FileException::class);

    $id = Str::uuid();
    $path = Filepond::path($id, 'image.png');
    $disk->put($path, 'foo bar');
    $invalidFile = UploadedFile::fromId($id);
    $invalidFile->move($disk->path('tmp/errors'));

    expect(fn () => $invalidFile->move($disk->path('tmp/errors')))
        ->toThrow(FileException::class);

    $errorProperty = new ReflectionClass(Symfony\Component\HttpFoundation\File\UploadedFile::class)
        ->getProperty('error');

    foreach ([
        UPLOAD_ERR_INI_SIZE => IniSizeFileException::class,
        UPLOAD_ERR_FORM_SIZE => FormSizeFileException::class,
        UPLOAD_ERR_PARTIAL => PartialFileException::class,
        UPLOAD_ERR_NO_FILE => NoFileException::class,
        UPLOAD_ERR_CANT_WRITE => CannotWriteFileException::class,
        UPLOAD_ERR_NO_TMP_DIR => NoTmpDirFileException::class,
        UPLOAD_ERR_EXTENSION => ExtensionFileException::class,
    ] as $error => $exception) {
        $errorProperty->setValue($invalidFile, $error);

        expect(fn () => $invalidFile->move($disk->path('tmp/errors')))
            ->toThrow($exception);
    }

    defer()->invoke();
    expect($disk->directoryMissing(Filepond::path($id)))->toBeTrue();
});
