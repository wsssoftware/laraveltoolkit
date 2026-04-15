<?php

use Illuminate\Support\Collection;
use Laraveltoolkit\Facades\Flash;
use Laraveltoolkit\Flash\FlashResource;
use Laraveltoolkit\Flash\Message;
use Laraveltoolkit\Flash\Severity;

it('can send flash', function () {
    Flash::success('ok');
    Flash::info('ok');
    Flash::warn('ok');
    Flash::error('ok');
    Flash::secondary('ok');
    Flash::contrast('ok');
    expect(Flash::getFlashed())
        ->toHaveCount(6)
        ->toBeInstanceOf(Collection::class)
        ->each
        ->toBeInstanceOf(Message::class);
});

it('test defaults', function () {

    config()->set('laraveltoolkit.flash.defaults.closable', true);
    config()->set('laraveltoolkit.flash.defaults.life', 1234);
    config()->set('laraveltoolkit.flash.defaults.group', 'abc');
    Flash::error('ok');
    $message = Flash::getFlashed()->first();
    expect($message->closable)
        ->toBeTrue()
        ->and($message->life)
        ->toEqual(1234)
        ->and($message->group)
        ->toEqual('abc');
});

it('can send messages with options', function () {

    Flash::error('ok')->withGroup('foo_bar');
    $messages = Flash::getFlashed();
    expect($messages->first()->group)
        ->toEqual('foo_bar');

    $this->flushSession();
    Flash::error('ok')->closable();
    Flash::error('ok')->unclosable();
    $messages = Flash::getFlashed();
    expect($messages->first()->closable)
        ->toBeTrue()
        ->and($messages->last()->closable)
        ->toBeFalse();

    $this->flushSession();
    Flash::error('ok')->withLife(4000);
    $messages = Flash::getFlashed();
    expect($messages->first()->life)
        ->toEqual(4000);
});

it('test resource', function () {
    $payload = FlashResource::make(
        Message::create(Severity::CONTRAST, 'abc', 'foo')
    )->resolve();

    expect($payload)
        ->toHaveKey('id')
        ->toHaveKey('severity')
        ->toHaveKey('summary')
        ->toHaveKey('detail')
        ->toHaveKey('group')
        ->not
        ->toHaveKey('life')
        ->not
        ->toHaveKey('closable');

    $payload = FlashResource::make(
        Message::create(Severity::CONTRAST, 'abc', 'foo')->closable()->withLife(2000)
    )->resolve();

    expect($payload)
        ->toHaveKey('id')
        ->toHaveKey('severity')
        ->toHaveKey('summary')
        ->toHaveKey('detail')
        ->toHaveKey('group')
        ->toHaveKey('life')
        ->toHaveKey('closable');
});

it('test assert flashed', function () {
    expect(fn () => Flash::assertFlashed())
        ->toThrow('Was expected a flash of "any" severity but was not found');

    $this->flushSession();
    Flash::success('ok');
    expect(fn () => Flash::assertFlashed())
        ->not
        ->toThrow('Was expected a flash of "any" severity but was not found');

    $this->flushSession();
    Flash::success('ok');
    expect(fn () => Flash::assertFlashed(Severity::INFO))
        ->toThrow('Was expected a flash of "info" severity but was not found')
        ->and(fn () => Flash::assertFlashed(Severity::SUCCESS))
        ->not
        ->toThrow('Was expected a flash of "success" severity but was not found');

    $this->flushSession();
    Flash::success('ok');
    expect(fn () => Flash::assertFlashed(countOrMessage: 'foo'))
        ->toThrow('Was expected a flash of "any" severity with detail of "foo" but was not found')
        ->and(fn () => Flash::assertFlashed(countOrMessage: 'ok'))
        ->not
        ->toThrow('Was expected a flash of "any" severity but was not found');

    $this->flushSession();
    Flash::success('ok');
    Flash::success('ok');
    expect(fn () => Flash::assertFlashed(countOrMessage: 3))
        ->toThrow('Was expected 3 flashes from "any" severity but was found 2')
        ->and(fn () => Flash::assertFlashed(countOrMessage: 2))
        ->not
        ->toThrow('Was expected 2 flashes from "any" severity but was found 2');
});

it('test assert not flashed', function () {
    $this->flushSession();
    expect(fn () => Flash::assertNotFlashed())
        ->not
        ->toThrow('Was expected none flashes of "any" severity but was found 1');

    $this->flushSession();
    Flash::success('ok');
    expect(fn () => Flash::assertNotFlashed())
        ->toThrow('Was expected none flashes of "any" severity but was found 1');

    $this->flushSession();
    Flash::success('ok');
    Flash::success('ok');
    Flash::success('ok');
    Flash::error('ok');
    expect(fn () => Flash::assertNotFlashed(Severity::SUCCESS))
        ->toThrow('Was expected none flashes of "success" severity but was found 3')
        ->and(fn () => Flash::assertNotFlashed(Severity::ERROR))
        ->toThrow('Was expected none flashes of "error" severity but was found 1')
        ->and(fn () => Flash::assertNotFlashed(Severity::INFO))
        ->not
        ->toThrow('Was expected none flashes of "info" severity but was found 1');
});
