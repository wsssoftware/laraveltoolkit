# Utilities

Laravel Toolkit registers a set of validation rules, Brazilian document and phone helpers, collection/string/number
macros, enum serializers, regex helpers, and fluent value objects. They are available after Laravel discovers the
package service provider.

## Validation rules

### CPF and CNPJ

`DocumentRule` accepts formatted or unformatted values and validates their check digits:

```php
use Laraveltoolkit\Rules\DocumentRule;

$validated = $request->validate([
    'cpf' => ['required', DocumentRule::cpf()],
    'cnpj' => ['required', DocumentRule::cnpj()],
    'cpf_or_cnpj' => ['nullable', DocumentRule::both()],
]);
```

Values must be strings. Add Laravel's `nullable` rule when the field may be absent.

### Brazilian phone numbers

`PhoneRule` can accept every supported format, one specific category, or a selected set:

```php
use Laraveltoolkit\Enum\Phone;
use Laraveltoolkit\Rules\PhoneRule;

$request->validate([
    'phone' => ['required', PhoneRule::all()],
    'mobile' => ['required', PhoneRule::mobile()],
    'contact' => [
        'required',
        PhoneRule::make(Phone::LANDLINE, Phone::MOBILE),
    ],
]);
```

Available categories are `GENERIC`, `LANDLINE`, `LOCAL_FARE`, `MOBILE`, `NON_REGIONAL`, and `PUBLIC_SERVICES`.

## Document and phone helpers

The `Document` and `Phone` enums expose validation, masking, type detection, labels, and fake values:

```php
use Laraveltoolkit\Enum\Document;
use Laraveltoolkit\Enum\Phone;

Document::guessType('123.456.789-00'); // Document::CPF
Document::CPF->mask('12345678900');    // 123.456.789-00
Document::CPF->unmask('123.456.789-00');
Document::CPF->validate('123.456.789-00');
Document::CPF->checkDigits('123456789');
Document::CPF->fake();

Phone::guessType('(43) 99999-9999'); // Phone::MOBILE
Phone::MOBILE->mask('43999999999');
Phone::MOBILE->unmask('(43) 99999-9999');
Phone::MOBILE->validate('(43) 99999-9999');
Phone::MOBILE->fake();
```

Fake values are intended for factories and tests, not for identity verification.

## String macros

### Masks

`Str::applyMask()` and its fluent equivalent consume input characters that match each placeholder:

| Placeholder | Accepts |
|:---:|---|
| `0` | Digit |
| `A` | Letter or digit |
| `S` | Letter |

Escape a literal placeholder with `\` in the mask.

```php
use Illuminate\Support\Str;

Str::applyMask('12345678901', '000.000.000-00');
// 123.456.789-01

str('ABC1234')->applyMask('SSS-0000')->toString();
// ABC-1234
```

### Personal names

`personalName()` normalizes capitalization while keeping common Portuguese connectors lowercase:

```php
Str::personalName('ALLAN MARIUCCI DE CARVALHO');
// Allan Mariucci de Carvalho

str('luiz ap. de carvalho')->personalName()->toString();
// Luiz Ap. de Carvalho
```

## Collection macros

Use the locale-aware sort helpers when accented strings should follow the application's locale:

```php
collect(['João', 'Allan', 'Álan'])->collatorSort()->values();
collect($users)->localeSortBy('name');
collect($users)->localeSortByDesc('name');
```

`collatorSort()` accepts PHP sort flags, a direction, and an optional locale:

```php
collect(['item 10', 'item 2'])->collatorSort(
    options: SORT_NATURAL,
    direction: SORT_ASC,
    locale: 'pt_BR',
)->values();
// ['item 2', 'item 10']
```

Convert keyed arrays or object collections to frontend-friendly option lists:

```php
collect(['draft' => 'Draft', 'published' => 'Published'])
    ->toValueLabelFromArray();
// [['value' => 'draft', 'label' => 'Draft'], ...]

$users->toValueLabelFromObject(
    label: 'name',
    value: 'id',
    keysToPreserve: ['avatar_url'],
);
```

## Number macros

```php
use Illuminate\Support\Number;

Number::spellCurrency(42.50, in: 'BRL', locale: 'pt_BR');

Number::roundAsMultipleOf(
    value: 1.025,
    step: 0.01,
    roundMode: \RoundingMode::HalfAwayFromZero,
)->value; // "1.03"
```

`roundAsMultipleOf()` returns a `BcMath\Number` and supports optional `min` and `max` boundaries.

## Enum helpers

Add `HasArrayableEnum` and implement `ArrayableEnum` on a backed enum to expose value/label payloads:

```php
use Laraveltoolkit\Enum\ArrayableEnum;
use Laraveltoolkit\Enum\HasArrayableEnum;

enum Status: string implements ArrayableEnum
{
    use HasArrayableEnum;

    case DRAFT = 'draft';
    case PUBLISHED = 'published';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
        };
    }
}

Status::toEnumArray();
// ['draft' => 'Draft', 'published' => 'Published']

Status::toValueLabel(only: ['published']);
// collect([['value' => 'published', 'label' => 'Published']])
```

Pass a sort flag and direction to sort by label. Leave `sortFlags` as `null` to preserve declaration order. The
`only` and `except` arguments filter by backed enum value.

## Regex facade

```php
use Laraveltoolkit\Facades\Regex;

Regex::getHashtags('Ship it #laravel #php');
Regex::isEmail('dev@example.com');
Regex::isHexColor('#334155');
Regex::isIPv4Address('127.0.0.1');
Regex::isIPv6Address('::1');
Regex::isIPAddress('::1');
Regex::isURL('https://example.com/docs');
Regex::isLikePhpVariableChars('valid_name');
Regex::isSequenceOfUniqueChar('aaaa');

Regex::onlyAlpha('Olá 123');                           // Olá
Regex::onlyAlphaNumeric('Item 42!', allowSpace: true); // Item 42
Regex::onlyNumeric('(43) 99999-9999');                // 43999999999
```

These are deliberately small format helpers. For security-sensitive email, URL, or IP validation, prefer PHP's
specialized filters and validation rules appropriate to your threat model.

## Schema and routing macros

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Schema::table('products', function (Blueprint $table) {
    $table->measurement('weight'); // JSON column
    $table->storedAsset('image');   // indexed UUID column
});

Route::getAndPost('/users', UsersController::class);
// Registers HEAD, GET, and POST for the same action.
```

The Eloquent Builder `primevueData()` macro is covered in the [PrimeVue Data guide](PRIMEVUE_DATA.md). Request
FilePond macros are covered in the [FilePond guide](FILEPOND.md).

## Extended fluent values

`ExtendedFluent` adds Eloquent-style `Attribute` accessors and mutators to Laravel's `Fluent`, including recursive
normalization for API output and storage. `AsExtendedFluent` casts a JSON model attribute to your fluent class.

```php
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laraveltoolkit\Support\AsExtendedFluent;
use Laraveltoolkit\Support\ExtendedFluent;

final class Preferences extends ExtendedFluent
{
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }
}

protected function casts(): array
{
    return [
        'preferences' => AsExtendedFluent::of(Preferences::class),
    ];
}
```

Use `toArray()` for presentation and `toStorageArray()` or `toStorageJson()` when backed enums must be reduced to
their scalar values.

---

[Back to the documentation index](README.md)
