<?php

namespace Laraveltoolkit\StoredAssets;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Laraveltoolkit\Facades\Regex;
use Laraveltoolkit\Facades\StoredAssets;
use League\Flysystem\UnableToReadFile;

class AssetIntent
{
    protected string $disk;

    protected string $key = 'default';

    protected FilenameStoreType $filenameStoreType;

    protected array $options = [];

    protected bool $public = false;

    private function __construct(
        public readonly string $pathname,
    ) {
        $this->disk = StoredAssets::defaultDisk();
        $this->filenameStoreType = StoredAssets::defaultFilenameStoreType();
    }

    public static function create(string $pathname): self
    {
        return new static($pathname);
    }

    public static function createFromContent(string $content): self
    {
        $resource = tmpfile();
        fwrite($resource, $content);
        fseek($resource, 0);
        register_shutdown_function(fn () => fclose($resource));

        return static::create(stream_get_meta_data($resource)['uri']);
    }

    public static function createFromResource(mixed $resource): self
    {
        throw_if(! is_resource($resource), Exception::class, 'Invalid resource');

        return static::create(stream_get_meta_data($resource)['uri']);
    }

    public static function createFromUploadedFile(UploadedFile $uploadedFile): self
    {
        return static::create($uploadedFile->getPathname());
    }

    public function asPrivate(): self
    {
        $this->public = false;

        return $this;
    }

    public function asPublic(): self
    {
        $this->public = true;

        return $this;
    }

    private function getContentStream(): mixed
    {
        error_clear_last();
        $contents = @fopen($this->pathname, 'rb');
        if ($contents === false) {
            throw UnableToReadFile::fromLocation($this->pathname, error_get_last()['message'] ?? '');
        }

        return $contents;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function store(string $uuid): Asset
    {
        $disk = Storage::disk($this->disk);
        if ($this->public) {
            $this->options['visibility'] = 'public';
        }
        $extension = File::guessExtension($this->pathname) ?? File::extension($this->pathname);
        $filename = $this->filenameStoreType->getFilename($this, $extension);
        $pathname = StoredAssets::path($uuid, $filename);
        $disk->put($pathname, $this->getContentStream(), $this->options);

        return new Asset(
            $uuid,
            $this->disk,
            $this->key,
            $pathname,
            $extension,
            File::mimeType($this->pathname),
            File::size($this->pathname),
        );
    }

    public function withDisk(string $disk): self
    {
        $this->disk = $disk;

        return $this;
    }

    public function withFilenameStoreType(FilenameStoreType $type): self
    {
        $this->filenameStoreType = $type;

        return $this;
    }

    public function withKey(string $key): self
    {
        throw_if(
            ! Regex::isLikePhpVariableChars($key),
            Exception::class,
            "\"$key\" is not a valid asset key."
        );
        $this->key = $key;

        return $this;
    }

    public function withOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }
}
