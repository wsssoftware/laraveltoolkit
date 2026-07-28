<?php

use Laraveltoolkit\Measurement\Enums\VolumeUnit;
use Laraveltoolkit\Measurement\Models\Volume;

it('describes and resolves localized volume units', function () {
    expect(VolumeUnit::LITER->name())->toBe('Litro')
        ->and(VolumeUnit::LITER->term())->toBe('L')
        ->and(VolumeUnit::CUBIC_FOOT->name('en'))->toBe('Cubic foot')
        ->and(VolumeUnit::CUBIC_FOOT->name())->toBe('Pé cúbico')
        ->and((string) VolumeUnit::LITER->reference())->toBe('1000000000')
        ->and((string) VolumeUnit::US_TEASPOON->reference())->toBe('4928921.59375')
        ->and((string) VolumeUnit::US_GALLON->reference())->toBe('3785411784')
        ->and(VolumeUnit::resolve('litro'))->toBe(VolumeUnit::LITER)
        ->and(VolumeUnit::resolve('L'))->toBe(VolumeUnit::LITER)
        ->and(VolumeUnit::resolve('in³'))->toBe(VolumeUnit::CUBIC_INCH)
        ->and(VolumeUnit::resolve('pol³'))->toBe(VolumeUnit::CUBIC_INCH)
        ->and(fn () => VolumeUnit::resolve('bucket'))
        ->toThrow(InvalidArgumentException::class, 'Unknown volume unit [bucket].');
});

it('converts volumes exactly between metric, cubic, US, and imperial units', function () {
    expect(volume(1)->to(VolumeUnit::MILLILITER)->value())->toBe(1000.0)
        ->and(volume(1, 'mL')->equals(volume(1, 'cm³')))->toBeTrue()
        ->and(volume(1, 'm³')->to(VolumeUnit::LITER)->value())->toBe(1000.0)
        ->and(volume(1, 'gal (US)')->to(VolumeUnit::LITER)->value())->toBe(3.785411784)
        ->and(volume(1, 'gal (imp)')->to(VolumeUnit::LITER)->value())->toBe(4.54609)
        ->and(volume(1, 'ft³')->to(VolumeUnit::LITER)->value())->toBe(28.316846592)
        ->and(volume(1, 'cup (US)')->to(VolumeUnit::MILLILITER)->value())->toBe(236.5882365)
        ->and(volume(3, 'tsp (US)')->equals(volume(1, 'tbsp (US)')))->toBeTrue()
        ->and(volume(2, 'tbsp (US)')->equals(volume(1, 'fl oz (US)')))->toBeTrue();
});

it('formats and operates on immutable volumes', function () {
    $volume = volume(1, 'L');
    $sum = $volume->add(volume(500, 'mL'));

    expect($volume->value())->toBe(1.0)
        ->and($sum->value())->toBe(1.5)
        ->and($sum->format())->toBe('1,5 L')
        ->and($sum->format(short: false))->toBe('1,5 litros')
        ->and(volume(2, 'ft³')->format(short: false, locale: 'en'))->toBe('2 cubic feet')
        ->and(volume(1, 'gal (US)')->referenceValue())->toBe(3_785_411_784);
});

it('serializes a volume and uses liters by default', function () {
    $volume = volume(2.5);

    expect($volume)->toBeInstanceOf(Volume::class)
        ->and($volume->unit())->toBe(VolumeUnit::LITER)
        ->and($volume->toArray())->toBe([
            'value' => 2.5,
            'unit' => 'liter',
        ]);
});

it('rejects arithmetic between different measurement dimensions', function () {
    expect(fn () => volume(1)->add(weight(1)))
        ->toThrow(InvalidArgumentException::class, 'Cannot operate on [volume] and [weight] measurements.');
});
