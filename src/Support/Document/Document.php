<?php

namespace Laraveltoolkit\Support\Document;

interface Document
{
    public function checkDigits(string $document): string;

    public function fake(): string;

    public function label(): string;

    public function mask(string $document): string;

    public function unmask(string $document): string;

    public function validate(string $document): bool;
}
