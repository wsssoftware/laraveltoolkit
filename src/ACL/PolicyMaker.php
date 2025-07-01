<?php

namespace Laraveltoolkit\ACL;

use Exception;
use Illuminate\Support\Collection;

class PolicyMaker
{
    public function __construct(
        public Collection $rules,
        public string $column,
        public string $name,
        public string $description,
    ) {
        throw_if(
            in_array($this->column, ['id', 'roles', 'created_at']),
            Exception::class,
            "$this->column is a reserved name and cannot used on key."
        );
    }

    public function rule(string $key, string $name, string $description, ?int $denyStatus = null): self
    {
        $this->rules->put($key, new Rule($key, $name, $description, $denyStatus));

        return $this;
    }

    public function cancel(?int $denyStatus = null): self
    {
        $this->rule(
            'cancel',
            __('laraveltoolkit::acl.cancel.name'),
            __('laraveltoolkit::acl.cancel.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus,
        );

        return $this;
    }

    public function create(?int $denyStatus = null): self
    {
        $this->rule(
            'create',
            __('laraveltoolkit::acl.create.name'),
            __('laraveltoolkit::acl.create.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus
        );

        return $this;
    }

    public function crud(?int $denyStatus = null): self
    {
        $this->create($denyStatus);
        $this->read($denyStatus);
        $this->update($denyStatus);
        $this->delete($denyStatus);

        return $this;
    }

    public function delete(?int $denyStatus = null): self
    {
        $this->rule(
            'delete',
            __('laraveltoolkit::acl.delete.name'),
            __('laraveltoolkit::acl.delete.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus,
        );

        return $this;
    }

    public function download(?int $denyStatus = null): self
    {
        $this->rule(
            'download',
            __('laraveltoolkit::acl.download.name'),
            __('laraveltoolkit::acl.download.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus,
        );

        return $this;
    }

    public function execute(?int $denyStatus = null): self
    {
        $this->rule(
            'execute',
            __('laraveltoolkit::acl.execute.name'),
            __('laraveltoolkit::acl.execute.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus,
        );

        return $this;
    }

    public function export(?int $denyStatus = null): self
    {
        $this->rule(
            'export',
            __('laraveltoolkit::acl.export.name'),
            __('laraveltoolkit::acl.export.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus,
        );

        return $this;
    }

    public function import(?int $denyStatus = null): self
    {
        $this->rule(
            'import',
            __('laraveltoolkit::acl.import.name'),
            __('laraveltoolkit::acl.import.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus,
        );

        return $this;
    }

    public function print(?int $denyStatus = null): self
    {
        $this->rule(
            'print',
            __('laraveltoolkit::acl.print.name'),
            __('laraveltoolkit::acl.print.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus
        );

        return $this;
    }

    public function read(?int $denyStatus = null): self
    {
        $this->rule(
            'read',
            __('laraveltoolkit::acl.read.name'),
            __('laraveltoolkit::acl.read.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus
        );

        return $this;
    }

    public function share(?int $denyStatus = null): self
    {
        $this->rule(
            'share',
            __('laraveltoolkit::acl.share.name'),
            __('laraveltoolkit::acl.share.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus,
        );

        return $this;
    }

    public function toPolicy(): Policy
    {
        return new Policy($this->rules, $this->column, $this->name, $this->description);
    }

    public function update(?int $denyStatus = null): self
    {
        $this->rule(
            'update',
            __('laraveltoolkit::acl.update.name'),
            __('laraveltoolkit::acl.update.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus
        );

        return $this;
    }

    public function upload(?int $denyStatus = null): self
    {
        $this->rule(
            'upload',
            __('laraveltoolkit::acl.upload.name'),
            __('laraveltoolkit::acl.upload.description', ['name' => mb_strtolower($this->name)]),
            $denyStatus
        );

        return $this;
    }
}
