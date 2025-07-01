<?php

namespace Laraveltoolkit\Filepond;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

readonly class Abortable implements Responsable
{
    private function __construct(
        public string $reason,
        public int $status = 500,
    ) {
        //
    }

    public static function make(string $reason, int $status = 500): static
    {
        return new static($reason, $status);
    }

    public function toResponse($request): Response
    {
        return response($this->reason, $this->status, ['Content-Type' => 'text/plain']);
    }
}
