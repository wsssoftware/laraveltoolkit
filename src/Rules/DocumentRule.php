<?php

namespace Laraveltoolkit\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Laraveltoolkit\Enum\Document;

readonly class DocumentRule implements ValidationRule
{
    protected function __construct(
        public Document $type = Document::GENERIC
    ) {
        //
    }

    public static function both(): self
    {
        return new self;
    }

    public static function cnpj(): self
    {
        return new self(Document::CNPJ);
    }

    public static function cpf(): self
    {
        return new self(Document::CPF);
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('validation.string')->translate();

            return;
        }
        $value = preg_replace('/[^0-9]/i', '', $value);
        $length = strlen($value);

        if ($this->type === Document::GENERIC) {
            match (true) {
                !in_array($length, [11, 14]) => $fail('laraveltoolkit::validation.document.generic.size')->translate(),
                !$this->type->validate($value) => $fail('laraveltoolkit::validation.document.generic.invalid')->translate(),
                default => true,
            };
        }
        if ($this->type === Document::CNPJ) {
            match (true) {
                $length !== 14 => $fail('laraveltoolkit::validation.document.cnpj.size')->translate(),
                !$this->type->validate($value) => $fail('laraveltoolkit::validation.document.cnpj.invalid')->translate(),
                default => true,
            };
        }
        if ($this->type === Document::CPF) {
            match (true) {
                $length !== 11 => $fail('laraveltoolkit::validation.document.cpf.size')->translate(),
                !$this->type->validate($value) => $fail('laraveltoolkit::validation.document.cpf.invalid')->translate(),
                default => true,
            };
        }
    }
}
