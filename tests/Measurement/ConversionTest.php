<?php

use Laraveltoolkit\Measurement\Contracts\Unit;
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

it('keeps critical conversion anchors stable', function (
    string $dimension,
    int|float $value,
    string $from,
    Unit $to,
    float $expected,
): void {
    $measurement = match ($dimension) {
        'length' => length($value, $from),
        'weight' => weight($value, $from),
        'volume' => volume($value, $from),
        'area' => area($value, $from),
        'temperature' => temperature($value, $from),
        'speed' => speed($value, $from),
        'duration' => duration($value, $from),
        'pressure' => pressure($value, $from),
        'energy' => energy($value, $from),
        'power' => power($value, $from),
    };

    expect($measurement->convert($to)->value())->toBe($expected);
})->with([
    'mile to meters' => ['length', 1, 'mi', LengthUnit::METER, 1609.344],
    'pound to kilograms' => ['weight', 1, 'lb', WeightUnit::KILOGRAM, 0.45359237],
    'US cup to milliliters' => ['volume', 1, 'cup (US)', VolumeUnit::MILLILITER, 236.5882365],
    'acre to square meters' => ['area', 1, 'ac', AreaUnit::SQUARE_METER, 4046.8564224],
    'freezing Fahrenheit to Celsius' => ['temperature', 32, '°F', TemperatureUnit::CELSIUS, 0.0],
    'knot to kilometers per hour' => ['speed', 1, 'kn', SpeedUnit::KILOMETER_PER_HOUR, 1.852],
    'Julian year to days' => ['duration', 1, 'yr', DurationUnit::DAY, 365.25],
    'atmosphere to kilopascals' => ['pressure', 1, 'atm', PressureUnit::KILOPASCAL, 101.325],
    'kilowatt-hour to megajoules' => ['energy', 1, 'kWh', EnergyUnit::MEGAJOULE, 3.6],
    'mechanical horsepower to watts' => ['power', 1, 'hp', PowerUnit::WATT, 745.699872],
]);

it('rejects a canonical value paired with an incompatible unit', function (
    string $measurementClass,
    Unit $unit,
    string $message,
): void {
    expect(fn () => $measurementClass::fromReferenceValue(1, $unit))
        ->toThrow(InvalidArgumentException::class, $message);
})->with([
    'length' => [Length::class, WeightUnit::GRAM, 'A length value requires a length unit.'],
    'weight' => [Weight::class, LengthUnit::METER, 'A weight value requires a weight unit.'],
    'volume' => [Volume::class, LengthUnit::METER, 'A volume value requires a volume unit.'],
    'area' => [Area::class, LengthUnit::METER, 'An area value requires an area unit.'],
    'temperature' => [Temperature::class, LengthUnit::METER, 'A temperature value requires a temperature unit.'],
    'speed' => [Speed::class, LengthUnit::METER, 'A speed value requires a speed unit.'],
    'duration' => [Duration::class, LengthUnit::METER, 'A duration value requires a duration unit.'],
    'pressure' => [Pressure::class, LengthUnit::METER, 'A pressure value requires a pressure unit.'],
    'energy' => [Energy::class, LengthUnit::METER, 'An energy value requires an energy unit.'],
    'power' => [Power::class, LengthUnit::METER, 'A power value requires a power unit.'],
]);
