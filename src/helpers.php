<?php

use BcMath\Number;
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

if (! function_exists('floatToBcNumber')) {
    /**
     * Converts a floating-point number to a BC Math compatible Number object.
     *
     * @param  float  $number  The floating-point number to convert.
     * @return Number The resulting Number object after conversion.
     */
    function floatToBcNumber(float $number): Number
    {
        $numberString = strval($number);

        $numberString = ! str_contains($numberString, 'E')
            ? $numberString
            : rtrim(number_format($number, ini_get('precision'), '.', ''), '0');

        return new Number($numberString);
    }
}

if (! function_exists('length')) {
    /**
     * Create an immutable length measurement.
     */
    function length(
        int|float $value,
        LengthUnit|string $unit = LengthUnit::METER,
    ): Length {
        $unit = is_string($unit) ? LengthUnit::resolve($unit) : $unit;

        return new Length($value, $unit);
    }
}

if (! function_exists('weight')) {
    /**
     * Create an immutable weight measurement.
     */
    function weight(
        int|float $value,
        WeightUnit|string $unit = WeightUnit::KILOGRAM,
    ): Weight {
        $unit = is_string($unit) ? WeightUnit::resolve($unit) : $unit;

        return new Weight($value, $unit);
    }
}

if (! function_exists('volume')) {
    /**
     * Create an immutable volume measurement.
     */
    function volume(
        int|float $value,
        VolumeUnit|string $unit = VolumeUnit::LITER,
    ): Volume {
        $unit = is_string($unit) ? VolumeUnit::resolve($unit) : $unit;

        return new Volume($value, $unit);
    }
}

if (! function_exists('area')) {
    /**
     * Create an immutable area measurement.
     */
    function area(
        int|float $value,
        AreaUnit|string $unit = AreaUnit::SQUARE_METER,
    ): Area {
        $unit = is_string($unit) ? AreaUnit::resolve($unit) : $unit;

        return new Area($value, $unit);
    }
}

if (! function_exists('temperature')) {
    /**
     * Create an immutable temperature measurement.
     */
    function temperature(
        int|float $value,
        TemperatureUnit|string $unit = TemperatureUnit::CELSIUS,
    ): Temperature {
        $unit = is_string($unit) ? TemperatureUnit::resolve($unit) : $unit;

        return new Temperature($value, $unit);
    }
}

if (! function_exists('speed')) {
    /**
     * Create an immutable speed measurement.
     */
    function speed(
        int|float $value,
        SpeedUnit|string $unit = SpeedUnit::KILOMETER_PER_HOUR,
    ): Speed {
        $unit = is_string($unit) ? SpeedUnit::resolve($unit) : $unit;

        return new Speed($value, $unit);
    }
}

if (! function_exists('duration')) {
    /**
     * Create an immutable duration measurement.
     */
    function duration(
        int|float $value,
        DurationUnit|string $unit = DurationUnit::SECOND,
    ): Duration {
        $unit = is_string($unit) ? DurationUnit::resolve($unit) : $unit;

        return new Duration($value, $unit);
    }
}

if (! function_exists('pressure')) {
    /**
     * Create an immutable pressure measurement.
     */
    function pressure(
        int|float $value,
        PressureUnit|string $unit = PressureUnit::PASCAL,
    ): Pressure {
        $unit = is_string($unit) ? PressureUnit::resolve($unit) : $unit;

        return new Pressure($value, $unit);
    }
}

if (! function_exists('energy')) {
    /**
     * Create an immutable energy measurement.
     */
    function energy(
        int|float $value,
        EnergyUnit|string $unit = EnergyUnit::JOULE,
    ): Energy {
        $unit = is_string($unit) ? EnergyUnit::resolve($unit) : $unit;

        return new Energy($value, $unit);
    }
}

if (! function_exists('power')) {
    /**
     * Create an immutable power measurement.
     */
    function power(
        int|float $value,
        PowerUnit|string $unit = PowerUnit::WATT,
    ): Power {
        $unit = is_string($unit) ? PowerUnit::resolve($unit) : $unit;

        return new Power($value, $unit);
    }
}
