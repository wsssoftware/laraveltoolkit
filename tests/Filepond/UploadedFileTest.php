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

it('test from id method on not valid values', function () {
    $empty1 = UploadedFile::fromId('');
    $empty2 = UploadedFile::fromId(null);
    $empty3 = UploadedFile::fromId(Str::uuid());
    $empty4 = UploadedFile::fromId('not a valid uuid');

    expect($empty1)
        ->toBeNull()
        ->and($empty2)
        ->toBeNull()
        ->and($empty3)
        ->toBeNull()
        ->and($empty4)
        ->toBeNull();
});

it('can get from id', function () {
    $id = Str::uuid();
    $diskName = Filepond::diskName();
    $disk = Storage::fake($diskName);
    $disk->put(Filepond::path($id, 'image.png'), 'foo bar');

    $file = UploadedFile::fromId($id);

    expect($file)
        ->toBeInstanceOf(UploadedFile::class)
        ->and($file->isValid())
        ->toBeTrue()
        ->and($file->move($disk->path('tmp')))
        ->toBeInstanceOf(File::class);

});

it('can move on test file', function () {
    $id = Str::uuid();
    $diskName = Filepond::diskName();
    $disk = Storage::fake($diskName);
    $disk->put(Filepond::path($id, 'image.png'), 'foo bar');
    $file = new UploadedFile($disk->path(Filepond::path($id, 'image.png')), 'image.png', test: true);
    expect($file)
        ->toBeInstanceOf(UploadedFile::class)
        ->and($file->isValid())
        ->toBeTrue()
        ->and($file->move($disk->path('tmp')))
        ->toBeInstanceOf(File::class);
});

it('can\'t move', function () {
    $id = Str::uuid();
    $diskName = Filepond::diskName();
    $disk = Storage::fake($diskName);
    $disk->put(Filepond::path($id, 'image.png'), 'foo bar');
    $file = UploadedFile::fromId($id);
    $disk->delete(Filepond::path($id, 'image.png'));

    $reflection = new ReflectionClass($file);

    $reflection->getProperty('forceValidOnTest')->setValue($file, true);

    expect($file)
        ->toBeInstanceOf(UploadedFile::class)
        ->and($file->isValid())
        ->toBeTrue()
        ->and(fn() => $file->move($disk->path('foo-bar/test')))
        ->toThrow(FileException::class);
});

it('can\'t move with other errors', function () {
    $id = Str::uuid();
    $diskName = Filepond::diskName();
    $disk = Storage::fake($diskName);
    $disk->put(Filepond::path($id, 'image.png'), 'foo bar');
    $file = UploadedFile::fromId($id);

    expect($file)
        ->toBeInstanceOf(UploadedFile::class)
        ->and($file->isValid())
        ->toBeTrue()
        ->and($file->move($disk->path('tmp')))
        ->toBeInstanceOf(File::class)
        ->and(fn() => $file->move($disk->path('tmp')))
        ->toThrow(FileException::class);

    $reflection = new ReflectionClass(Symfony\Component\HttpFoundation\File\UploadedFile::class);
    $property = $reflection->getProperty('error');

    $property->setValue($file, UPLOAD_ERR_INI_SIZE);
    expect(fn() => $file->move($disk->path('tmp')))
        ->toThrow(IniSizeFileException::class);

    $property->setValue($file, UPLOAD_ERR_FORM_SIZE);
    expect(fn() => $file->move($disk->path('tmp')))
        ->toThrow(FormSizeFileException::class);

    $property->setValue($file, UPLOAD_ERR_PARTIAL);
    expect(fn() => $file->move($disk->path('tmp')))
        ->toThrow(PartialFileException::class);

    $property->setValue($file, UPLOAD_ERR_NO_FILE);
    expect(fn() => $file->move($disk->path('tmp')))
        ->toThrow(NoFileException::class);

    $property->setValue($file, UPLOAD_ERR_CANT_WRITE);
    expect(fn() => $file->move($disk->path('tmp')))
        ->toThrow(CannotWriteFileException::class);

    $property->setValue($file, UPLOAD_ERR_NO_TMP_DIR);
    expect(fn() => $file->move($disk->path('tmp')))
        ->toThrow(NoTmpDirFileException::class);

    $property->setValue($file, UPLOAD_ERR_EXTENSION);
    expect(fn() => $file->move($disk->path('tmp')))
        ->toThrow(ExtensionFileException::class);
});
