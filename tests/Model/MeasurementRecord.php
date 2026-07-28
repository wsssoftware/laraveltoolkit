<?php

namespace Laraveltoolkit\Tests\Model;

use Illuminate\Database\Eloquent\Model;
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

final class MeasurementRecord extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'measurement_records';

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
}
