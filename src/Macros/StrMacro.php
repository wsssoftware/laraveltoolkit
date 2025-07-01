<?php

namespace Laraveltoolkit\Macros;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class StrMacro
{
    public function __invoke(): void
    {
        $this->applyMask();
        $this->personalName();
    }

    public function applyMask(): void
    {
        Str::macro('applyMask', function (string $input, string $mask): string {
            $result = '';
            $inputIndex = 0;
            $escape = false;

            foreach (str_split($mask) as $currentMaskChar) {
                if ($currentMaskChar === '\\' && ! $escape) {
                    $escape = true;

                    continue;
                }
                if ($escape) {
                    $result .= $currentMaskChar;
                    $escape = false;

                    continue;
                }
                if ($inputIndex >= strlen($input)) {
                    break;
                }

                while ($inputIndex + 1 <= strlen($input)) {
                    $charInput = $input[$inputIndex];
                    if ($currentMaskChar === '0' && ctype_digit($charInput)) {
                        $result .= $charInput;
                        $inputIndex++;
                        break;
                    } elseif ($currentMaskChar === 'A' && ctype_alnum($charInput)) {
                        $result .= $charInput;
                        $inputIndex++;
                        break;
                    } elseif ($currentMaskChar === 'S' && ctype_alpha($charInput)) {
                        $result .= $charInput;
                        $inputIndex++;
                        break;
                    } elseif (! in_array($currentMaskChar, ['0', 'A', 'S'])) {
                        $result .= $currentMaskChar;
                        break;
                    }
                    $inputIndex++;
                }

            }

            return $result;
        });

        Stringable::macro('applyMask', function (string $mask): Stringable {
            return new Stringable(Str::applyMask($this->value, $mask));
        });
    }

    public function personalName(): void
    {
        Str::macro('personalName', function (?string $name): ?string {
            if ($name === null) {
                return null;
            }
            $toIgnore = [
                'da', 'de', 'di', 'do', 'du',
                'das', 'des', 'dis', 'dos', 'dus',
                'a', 'e', 'i', 'o', 'u',
                'as', 'es', 'is', 'os', 'us',
                'd’',
            ];
            $words = str($name)->deduplicate()
                ->explode(' ')
                ->map(function (string $word) use ($toIgnore) {
                    $word = mb_strtolower($word);
                    if (! in_array($word, $toIgnore)) {
                        $word = mb_strtoupper(mb_substr($word, 0, 1)).mb_substr($word, 1);
                    }

                    return $word;
                });

            return $words->implode(' ');
        });
        Stringable::macro('personalName', function (): Stringable {
            return new Stringable(Str::personalName($this->value));
        });
    }
}
