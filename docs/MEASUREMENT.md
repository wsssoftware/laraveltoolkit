# Measurement

Measurement provides immutable-style value objects for typed measurements. Length, weight, volume, area, temperature,
speed, duration, pressure, energy, and power are currently supported.

The feature requires PHP's BCMath and Intl extensions. All measurement helpers are loaded automatically after Composer
installs the package.

```php
use Laraveltoolkit\Measurement\Enums\AreaUnit;
use Laraveltoolkit\Measurement\Enums\DurationUnit;
use Laraveltoolkit\Measurement\Enums\EnergyUnit;
use Laraveltoolkit\Measurement\Enums\LengthUnit;
use Laraveltoolkit\Measurement\Enums\PowerUnit;
use Laraveltoolkit\Measurement\Enums\PressureUnit;
use Laraveltoolkit\Measurement\Enums\SpeedUnit;
use Laraveltoolkit\Measurement\Enums\TemperatureUnit;
use Laraveltoolkit\Measurement\Enums\VolumeUnit;
use Laraveltoolkit\Measurement\Enums\WeightUnit;
use Laraveltoolkit\Measurement\Models\Length;

$length = length(2.54, 'cm');
$sameLength = new Length(1, LengthUnit::INCH);

$length->equals($sameLength); // true
$length->to(LengthUnit::MILLIMETER)->value(); // 25.4

weight(1, 'lb')->to(WeightUnit::KILOGRAM)->value(); // 0.45359237
volume(1, 'gal (US)')->to(VolumeUnit::LITER)->value(); // 3.785411784
area(1, 'ha')->to(AreaUnit::SQUARE_METER)->value(); // 10000.0
temperature(0, '°C')->to(TemperatureUnit::FAHRENHEIT)->value(); // 32.0
speed(36, 'km/h')->to(SpeedUnit::METER_PER_SECOND)->value(); // 10.0
duration(1, 'h')->to(DurationUnit::MINUTE)->value(); // 60.0
pressure(1, 'atm')->to(PressureUnit::KILOPASCAL)->value(); // 101.325
energy(1, 'kWh')->to(EnergyUnit::JOULE)->value(); // 3600000.0
power(1, 'kW')->to(PowerUnit::WATT)->value(); // 1000.0
```

The `length()`, `weight()`, `volume()`, `area()`, `temperature()`, and `speed()` helpers accept their respective unit
enum or a unit name, value, or short term. `duration()`, `pressure()`, `energy()`, and `power()` follow the same rule.
Their defaults are meter, kilogram, liter, square meter, Celsius, kilometer per hour, second, pascal, joule, and watt.

## Length units

`LengthUnit` includes metric units from nanometer through kilometer, plus inch, foot, yard, mile, and nautical mile.
Each case exposes:

```php
LengthUnit::CENTIMETER->name();      // Centimeter (en) or Centímetro (pt_BR)
LengthUnit::CENTIMETER->term();      // cm
(string) LengthUnit::CENTIMETER->reference(); // "10000000" nanometers
```

Names and short terms use the current Laravel locale. The package provides `en` and `pt_BR` translations. Resolution
accepts translated aliases from both locales, so `in` and `pol` resolve to `LengthUnit::INCH` independently of the
current locale. Applications may override the package translation files using Laravel's standard translation
publishing and overriding mechanisms.

## Weight units

`WeightUnit` uses nanograms as its canonical reference and includes nanogram, microgram, milligram, gram,
kilogram, metric ton, ounce, pound, and stone. Despite the familiar API name, this dimension represents mass rather
than physical force.

```php
weight(1, 'kg')->to(WeightUnit::GRAM)->value(); // 1000.0
weight(16, 'oz')->equals(weight(1, 'lb'));      // true
weight(2, 'lb')->format(short: false, locale: 'en'); // 2 pounds
```

## Volume units

`VolumeUnit` uses nanoliters as its canonical reference. It includes metric liter units, cubic millimeter, cubic
centimeter, cubic meter, cubic inch, cubic foot, US teaspoon/tablespoon/fluid ounce/cup/pint/quart/gallon, and imperial
pint/gallon. Decimal reference factors are preserved by BCMath, so customary units smaller than one nanoliter remain
exact.

```php
volume(1, 'mL')->equals(volume(1, 'cm³')); // true
volume(1, 'm³')->to(VolumeUnit::LITER)->value(); // 1000.0
volume(1, 'gal (imp)')->to(VolumeUnit::LITER)->value(); // 4.54609
volume(1, 'cup (US)')->to(VolumeUnit::MILLILITER)->value(); // 236.5882365
volume(3, 'tsp (US)')->equals(volume(1, 'tbsp (US)')); // true
```

## Area units

`AreaUnit` includes square millimeter, centimeter, decimeter, meter and kilometer, plus are, hectare, square inch,
foot, yard and mile, and acre. Its internal reference tick is one hundredth of a square millimeter, allowing exact
metric and imperial definitions.

```php
area(1, 'ha')->to(AreaUnit::SQUARE_METER)->value(); // 10000.0
area(1, 'ac')->to(AreaUnit::SQUARE_FOOT)->value();  // 43560.0
area(144, 'in²')->equals(area(1, 'ft²'));           // true
```

## Temperature units

`TemperatureUnit` includes Celsius, Fahrenheit, Kelvin, and Rankine. Temperature conversion supports both scale and
zero-point offsets. The canonical tick is one ninth of a nanokelvin, making the four scale definitions exact.

```php
temperature(0, '°C')->to(TemperatureUnit::FAHRENHEIT)->value(); // 32.0
temperature(-40, '°C')->equals(temperature(-40, '°F'));         // true
temperature(0, 'K')->to(TemperatureUnit::RANKINE)->value();     // 0.0
```

Scalar addition and subtraction represent deltas in the current scale. Multiplication and division operate on the
displayed value. Adding or subtracting two absolute temperature objects is rejected because the result would have
ambiguous semantics; use `difference()` instead:

```php
temperature(10, '°C')->add(5)->value(); // 15.0
temperature(50, '°F')->add(10)->value(); // 60.0
temperature(100, '°C')->difference(temperature(32, '°F')); // 100.0 °C difference
```

## Speed units

`SpeedUnit` includes millimeter, centimeter, meter and kilometer per second, meter per minute, kilometer per hour,
foot per second, mile per hour, and knot. Its canonical reference is measured in micrometers per hour, keeping common
metric, imperial, and nautical conversions exact.

```php
speed(36, 'km/h')->to(SpeedUnit::METER_PER_SECOND)->value(); // 10.0
speed(60, 'mph')->to(SpeedUnit::KILOMETER_PER_HOUR)->value(); // 96.56064
speed(1, 'kn')->to(SpeedUnit::KILOMETER_PER_HOUR)->value(); // 1.852
```

## Duration units

Elapsed time is represented by `Duration` rather than an absolute date or clock time. `DurationUnit` includes
nanosecond, microsecond, millisecond, second, minute, hour, day, week, Julian month, and Julian year, using nanoseconds
as its canonical reference. The helper is named `duration()` because PHP already defines the global `time()` function.

```php
duration(1, 'h')->to(DurationUnit::MINUTE)->value(); // 60.0
duration(1, 'wk')->to(DurationUnit::DAY)->value();   // 7.0
duration(1, 'month')->to(DurationUnit::DAY)->value(); // 30.4375
duration(1, 'year')->to(DurationUnit::DAY)->value();  // 365.25
```

`MONTH` and `YEAR` are fixed elapsed-time units based on a Julian year: one year is exactly 365.25 days and one month
is one twelfth of that year, or 30.4375 days. They do not represent variable Gregorian calendar periods. Use Laravel
or Carbon date APIs when calendar-aware arithmetic such as “one month after January 31” is required.

## Pressure units

`PressureUnit` includes pascal, hectopascal, kilopascal, megapascal, millibar, bar, standard atmosphere, psi, torr,
millimeter of mercury, and inch of mercury. The canonical reference is one nanopascal.

```php
pressure(1, 'bar')->to(PressureUnit::KILOPASCAL)->value(); // 100.0
pressure(1, 'atm')->to(PressureUnit::KILOPASCAL)->value(); // 101.325
pressure(1, 'hPa')->equals(pressure(1, 'mbar'));            // true
```

SI units, bar, and standard atmosphere are exact. `psi`, `torr`, `mmHg`, and `inHg` are quantized to the nearest
nanopascal. `torr` and `mmHg` remain separate units because their standard definitions differ slightly.

## Energy units

`EnergyUnit` includes joule through gigajoule, watt-hour through megawatt-hour, calorie, kilocalorie, and British
thermal unit. The canonical reference is one microjoule.

```php
energy(1, 'kWh')->to(EnergyUnit::JOULE)->value(); // 3600000.0
energy(1, 'kcal')->to(EnergyUnit::JOULE)->value(); // 4184.0
energy(1, 'kWh')->power(duration(1, 'h'), PowerUnit::KILOWATT)->value(); // 1.0
```

Metric, watt-hour, and thermochemical calorie conversions are exact. The international-table BTU is quantized to the
nearest microjoule.

## Power units

`PowerUnit` includes microwatt through gigawatt, mechanical horsepower, metric horsepower, and BTU per hour. The
canonical reference is one microwatt.

```php
power(1, 'kW')->to(PowerUnit::WATT)->value(); // 1000.0
power(1, 'hp')->to(PowerUnit::WATT)->value(); // 745.699872
power(2, 'kW')->energy(duration(30, 'min'), EnergyUnit::KILOWATT_HOUR)->value(); // 1.0
```

Mechanical horsepower and BTU per hour are quantized to the nearest microwatt; metric horsepower is exact at this
precision.

## Arithmetic and conversion

Measurements are not changed in place. Every operation returns a new object while preserving the current display
unit.

```php
$length = length(1, 'm');

$length->add(length(50, 'cm'))->value(); // 1.5
$length->sub(25)->value();               // -24.0; scalar values use the current unit
$length->mul(2)->value();                // 2.0
$length->div(4)->value();                // 0.25
$length->to(LengthUnit::CENTIMETER)->value(); // 100.0
$length->toFloat();                      // 1.0
$length->toInteger();                    // 1
$length->round(2);                       // 1.0
$length->gt(length(99, 'cm'));           // true; measurement units are normalized
$length->gte(1);                         // true; scalars use the current unit (meters)
$length->lt(1.01);                       // true
$length->lte(length(100, 'cm'));         // true
```

`toFloat()` returns the displayed value explicitly as a `float`. `toInteger()` rounds the displayed value to an
integer using `RoundingMode::HalfAwayFromZero` by default and throws an `OverflowException` when the result is outside
PHP's native integer range. `round()` returns the displayed value as a `float` rounded to the requested precision.
Both rounding methods accept a different `RoundingMode` as their last argument.

`compare()`, `equals()`, `gt()`, `gte()`, `lt()`, and `lte()` accept another compatible measurement or an `int` or
`float`. Measurements are compared through their canonical reference unit, while scalar values are interpreted as
absolute values in the receiving object's current unit. Incompatible measurement dimensions throw an
`InvalidArgumentException`. This distinction is especially important for temperatures, where a scalar is an absolute
value rather than a temperature delta.

Use `referenceValue()` to inspect the dimension's canonical value. It returns an `int` for an integral value within
PHP's native integer range, an exact decimal `string` for a larger integer, and a `float` for a fractional value.
`referenceValueString()` always returns the normalized exact decimal representation and is the safest choice for
storage or interchange.

## Formatting

`format()` follows the precision and maximum precision behavior from Laravel's `Number::format()`, then adds the
localized unit as a postfix. Its additional `short` parameter comes before `locale`. Short postfixes are used by
default; pass `short: false` for the lowercase complete name, pluralized according to the displayed value.

```php
$length = length(1234.5, 'in');

$length->format();                              // 1.234,5 pol (pt_BR)
$length->format(precision: 2);                  // 1.234,50 pol (pt_BR)
$length->format(locale: 'en');                  // 1,234.5 in
$length->format(null, null, false, 'en');       // 1,234.5 inches
$length->format(locale: 'pt_BR', short: false); // 1.234,5 polegadas
$length->formatWithoutUnit();                   // 1.234,5
$length->formatWithoutUnit(locale: 'en');       // 1,234.5
```

When `locale` is omitted, the current Laravel application locale formats both the number and its postfix.
Abbreviated postfixes are never pluralized. Complete postfixes use the number after display precision is applied, so
`length(1.4, 'm')->format(precision: 0, short: false)` returns `1 metro`.
Use `formatWithoutUnit()` to apply the same localized precision rules without appending either the abbreviated or
complete unit.

## Eloquent casting

Every concrete measurement model is an Eloquent `Castable`. Add the measurement class directly to the model's casts:

```php
use Laraveltoolkit\Measurement\Models\Area;
use Laraveltoolkit\Measurement\Models\Duration;
use Laraveltoolkit\Measurement\Models\Energy;
use Laraveltoolkit\Measurement\Models\Length;
use Laraveltoolkit\Measurement\Models\Power;
use Laraveltoolkit\Measurement\Models\Pressure;
use Laraveltoolkit\Measurement\Models\Speed;
use Laraveltoolkit\Measurement\Models\Temperature;
use Laraveltoolkit\Measurement\Models\Volume;
use Laraveltoolkit\Measurement\Models\Weight;

protected function casts(): array
{
    return [
        'length' => Length::class,
        'weight' => Weight::class,
        'volume' => Volume::class,
        'area' => Area::class,
        'temperature' => Temperature::class,
        'speed' => Speed::class,
        'duration' => Duration::class,
        'pressure' => Pressure::class,
        'energy' => Energy::class,
        'power' => Power::class,
    ];
}
```

The package registers a `measurement()` Blueprint macro that creates the JSON column expected by the cast. All regular
column modifiers remain available:

```php
$table->measurement('length')->nullable();
$table->measurement('weight')->nullable();
$table->measurement('volume')->nullable();
$table->measurement('area')->nullable();
$table->measurement('temperature')->nullable();
$table->measurement('speed')->nullable();
$table->measurement('duration')->nullable();
$table->measurement('pressure')->nullable();
$table->measurement('energy')->nullable();
$table->measurement('power')->nullable();
```

A manually declared `json` or `text` column remains compatible with the cast when a project needs database-specific
column behavior.

Assign and read the attribute as a regular measurement object:

```php
$model->length = length(2.54, 'cm');
$model->save();

$model->refresh();
$model->length->equals(length(1, 'in')); // true
```

The cast stores the exact canonical decimal as a JSON string together with the selected unit, rather than a formatted
or floating-point value:

```json
{"reference_value":"25400000","unit":"centimeter"}
```

`null` values are preserved. Invalid stored payloads and assignments from another type are rejected instead of being
silently coerced. New measurement models extending `Number` inherit the same cast; they only need to implement their
unit resolution and construction contracts. The explicit `MeasurementCast::of(Length::class)` syntax is also
available, although using `Length::class` directly is shorter. Existing payloads whose `reference_value` is a JSON
integer remain readable; newly written payloads always use a string so databases and JSON decoders cannot truncate a
large value.

## Precision and range limitations

Measurements store their canonical value in PHP's `BcMath\Number`. The canonical references are nanometer for length,
nanogram for weight, nanoliter for volume, one hundredth of a square millimeter for area, one ninth of a nanokelvin for
temperature, micrometer per hour for speed, nanosecond for duration, nanopascal for pressure, microjoule for energy,
and microwatt for power. Conversion, addition, subtraction, multiplication, division, and comparison are all performed
by BCMath, avoiding binary floating-point drift and the magnitude limit of `PHP_INT_MAX`.

There are still deliberate presentation and precision boundaries:

- Internal results retain up to 24 decimal places in the canonical reference. Construction, scalar
  addition/subtraction, multiplication, and division use `RoundingMode::HalfAwayFromZero` by default. Pass another PHP
  `RoundingMode` to the operation when a different rule is required.
- Magnitude is no longer limited to a native 64-bit integer. For example, a duration of thousands of Julian years can
  be represented and persisted without overflow.
- Inputs are accepted as `int|float`. `value()` returns a native `float` for convenient application use and
  `format()` returns a localized string. Consequently, extremely large values or values with many significant digits
  can lose precision when exposed as a float even though the internal value remains exact to its configured decimal
  scale.
- Use `equals()` and `compare()` for exact internal comparisons, and `referenceValueString()` for exact persistence or
  interchange. `referenceValue()` is a convenience accessor whose fractional result is a float.
- Division by zero throws `DivisionByZeroError`.

Example with an explicit rounding policy:

```php
length(2, 'nm')->div(3)->referenceValueString();
// 0.666666666666666666666667 (HalfAwayFromZero at 24 decimal places)

length(2, 'nm')->div(3, RoundingMode::TowardsZero)->referenceValueString();
// 0.666666666666666666666666
```

---

[Back to the documentation index](README.md)
