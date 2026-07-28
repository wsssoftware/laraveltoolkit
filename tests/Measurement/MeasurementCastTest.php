<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laraveltoolkit\Measurement\Casts\MeasurementCast;
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
use Laraveltoolkit\Tests\Model\MeasurementRecord;

beforeEach(function (): void {
    Schema::create('measurement_records', function (Blueprint $table): void {
        $table->id();
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
    });
});

afterEach(function (): void {
    Schema::dropIfExists('measurement_records');
});

it('persists and hydrates a measurement without losing precision', function (): void {
    $record = MeasurementRecord::query()->create([
        'length' => new Length(2.54, LengthUnit::CENTIMETER),
    ]);

    expect(DB::table('measurement_records')->value('length'))
        ->toBe('{"reference_value":"25400000","unit":"centimeter"}');

    $record = MeasurementRecord::query()->findOrFail($record->getKey());

    expect($record->length)
        ->toBeInstanceOf(Length::class)
        ->and($record->length->unit())->toBe(LengthUnit::CENTIMETER)
        ->and($record->length->value())->toBe(2.54)
        ->and($record->length->equals(new Length(1, LengthUnit::INCH)))->toBeTrue();
});

it('preserves null values', function (): void {
    $record = MeasurementRecord::query()->create(['length' => null]);

    expect($record->fresh()->length)->toBeNull();
});

it('rejects values from an incompatible measurement', function (): void {
    $record = new MeasurementRecord;

    expect(fn () => $record->length = 10)
        ->toThrow(InvalidArgumentException::class, 'must be an instance of');
});

it('rejects malformed stored payloads', function (string $payload, string $message): void {
    $id = DB::table('measurement_records')->insertGetId(['length' => $payload]);

    expect(fn () => MeasurementRecord::query()->findOrFail($id)->length)
        ->toThrow(UnexpectedValueException::class, $message);
})->with([
    ['not-json', 'contains invalid JSON'],
    ['[]', 'must contain a numeric [reference_value] and a string [unit]'],
    ['{"reference_value":"potato","unit":"meter"}', 'must contain a valid decimal [reference_value]'],
]);

it('makes every measurement model castable and supports explicit cast syntax', function (): void {
    expect(Length::castUsing([]))->toBeInstanceOf(MeasurementCast::class)
        ->and(Weight::castUsing([]))->toBeInstanceOf(MeasurementCast::class)
        ->and(Volume::castUsing([]))->toBeInstanceOf(MeasurementCast::class)
        ->and(Area::castUsing([]))->toBeInstanceOf(MeasurementCast::class)
        ->and(Temperature::castUsing([]))->toBeInstanceOf(MeasurementCast::class)
        ->and(Speed::castUsing([]))->toBeInstanceOf(MeasurementCast::class)
        ->and(Duration::castUsing([]))->toBeInstanceOf(MeasurementCast::class)
        ->and(Pressure::castUsing([]))->toBeInstanceOf(MeasurementCast::class)
        ->and(Energy::castUsing([]))->toBeInstanceOf(MeasurementCast::class)
        ->and(Power::castUsing([]))->toBeInstanceOf(MeasurementCast::class)
        ->and(MeasurementCast::of(Length::class))->toBe(MeasurementCast::class.':'.Length::class);
});

it('supports direct cast values and rejects invalid cast configuration', function (): void {
    $model = new MeasurementRecord;
    $cast = new MeasurementCast(Length::class);
    $length = length(1);

    expect($cast->get($model, 'length', null, []))->toBeNull()
        ->and($cast->get($model, 'length', $length, []))->toBe($length)
        ->and($cast->get($model, 'length', [
            'reference_value' => '1000000000',
            'unit' => 'meter',
        ], [])->equals($length))->toBeTrue()
        ->and(fn () => $cast->get($model, 'length', 42, []))
        ->toThrow(UnexpectedValueException::class, 'must be a JSON string or an array')
        ->and(fn () => $cast->get($model, 'length', '42', []))
        ->toThrow(UnexpectedValueException::class, 'must contain a JSON object')
        ->and(fn () => new MeasurementCast(stdClass::class))
        ->toThrow(InvalidArgumentException::class, 'must extend');
});

it('uses the same cast for duration, pressure, energy, and power measurements', function (): void {
    $record = MeasurementRecord::query()->create([
        'duration' => new Duration(1000, DurationUnit::YEAR),
        'pressure' => new Pressure(1, PressureUnit::ATMOSPHERE),
        'energy' => new Energy(1, EnergyUnit::KILOWATT_HOUR),
        'power' => new Power(1, PowerUnit::MECHANICAL_HORSEPOWER),
    ]);

    $stored = DB::table('measurement_records')->find($record->getKey());

    expect($stored->duration)->toBe('{"reference_value":"31557600000000000000","unit":"year"}')
        ->and($stored->pressure)->toBe('{"reference_value":"101325000000000","unit":"atmosphere"}')
        ->and($stored->energy)->toBe('{"reference_value":"3600000000000","unit":"kilowatt_hour"}')
        ->and($stored->power)->toBe('{"reference_value":"745699872","unit":"mechanical_horsepower"}');

    $record = $record->fresh();

    expect($record->duration->to(DurationUnit::YEAR)->value())->toBe(1000.0)
        ->and($record->pressure->to(PressureUnit::KILOPASCAL)->value())->toBe(101.325)
        ->and($record->energy->to(EnergyUnit::JOULE)->value())->toBe(3_600_000.0)
        ->and($record->power->to(PowerUnit::WATT)->value())->toBe(745.699872);
});

it('uses the same cast for weight and volume measurements', function (): void {
    $record = MeasurementRecord::query()->create([
        'weight' => new Weight(1, WeightUnit::POUND),
        'volume' => new Volume(1, VolumeUnit::US_GALLON),
    ]);

    $stored = DB::table('measurement_records')->find($record->getKey());

    expect($stored->weight)->toBe('{"reference_value":"453592370000","unit":"pound"}')
        ->and($stored->volume)->toBe('{"reference_value":"3785411784","unit":"us_gallon"}');

    $record = $record->fresh();

    expect($record->weight)->toBeInstanceOf(Weight::class)
        ->and($record->weight->equals(weight(16, 'oz')))->toBeTrue()
        ->and($record->volume)->toBeInstanceOf(Volume::class)
        ->and($record->volume->to(VolumeUnit::LITER)->value())->toBe(3.785411784);
});

it('uses the same cast for area, temperature, and speed measurements', function (): void {
    $record = MeasurementRecord::query()->create([
        'area' => new Area(1, AreaUnit::ACRE),
        'temperature' => new Temperature(32, TemperatureUnit::FAHRENHEIT),
        'speed' => new Speed(60, SpeedUnit::MILE_PER_HOUR),
    ]);

    $stored = DB::table('measurement_records')->find($record->getKey());

    expect($stored->area)->toBe('{"reference_value":"404685642240","unit":"acre"}')
        ->and($stored->temperature)->toBe('{"reference_value":"2458350000000","unit":"fahrenheit"}')
        ->and($stored->speed)->toBe('{"reference_value":"96560640000","unit":"mile_per_hour"}');

    $record = $record->fresh();

    expect($record->area->to(AreaUnit::SQUARE_FOOT)->value())->toBe(43_560.0)
        ->and($record->temperature->to(TemperatureUnit::CELSIUS)->value())->toBe(0.0)
        ->and($record->speed->to(SpeedUnit::KILOMETER_PER_HOUR)->value())->toBe(96.56064);
});
