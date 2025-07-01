<?php

namespace Laraveltoolkit\Support\Phone;

interface Phone
{
    public function appearsToBe(string $phone): string;

    public function fake(): string;

    public function label(): string;

    public function mask(string $phone): string;

    public function unmask(string $phone): string;

    public function validate(string $phone): bool;
}
