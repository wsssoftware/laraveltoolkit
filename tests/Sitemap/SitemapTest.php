<?php

use Illuminate\Support\Collection;
use Laraveltoolkit\Facades\Sitemap;
use Workbench\App\Models\User;

it('can add group', function () {
    /** @var Collection $items */
    $items = (new ReflectionClass(Sitemap::getFacadeRoot()::class))
        ->getProperty('items')
        ->getValue(Sitemap::getFacadeRoot());

    expect($items)
        ->toHaveCount(0)
        ->and(Sitemap::addIndex('abc')->addIndex('abc')->addIndex('abc2'))
        ->toBeInstanceOf(Laraveltoolkit\Sitemap\Sitemap::class)
        ->and($items)
        ->toHaveCount(2);
});

it('can add url', function () {
    /** @var Collection $items */
    $items = (new ReflectionClass(Sitemap::getFacadeRoot()::class))
        ->getProperty('items')
        ->getValue(Sitemap::getFacadeRoot());

    expect($items)
        ->toHaveCount(0)
        ->and(Sitemap::addUrl('google.com.br')->addUrl('google.com.br')->addUrl('cade.com.br'))
        ->toBeInstanceOf(Laraveltoolkit\Sitemap\Sitemap::class)
        ->and($items)
        ->toHaveCount(2);
});

it('can from query', function () {
    /** @var Collection $closureRepositories */
    $closureRepositories = (new ReflectionClass(Sitemap::getFacadeRoot()::class))
        ->getProperty('closureRepositories')
        ->getValue(Sitemap::getFacadeRoot());

    expect($closureRepositories)
        ->toHaveCount(0)
        ->and(Sitemap::fromQuery(User::query(), fn () => Sitemap::addUrl('foo.com')))
        ->toBeInstanceOf(Laraveltoolkit\Sitemap\Sitemap::class)
        ->and($closureRepositories)
        ->toHaveCount(1);
});

it('can from collection', function () {
    /** @var Collection $closureRepositories */
    $closureRepositories = (new ReflectionClass(Sitemap::getFacadeRoot()::class))
        ->getProperty('closureRepositories')
        ->getValue(Sitemap::getFacadeRoot());

    expect($closureRepositories)
        ->toHaveCount(0)
        ->and(Sitemap::fromCollection(collect([1, 2, 3]), fn () => Sitemap::addUrl('foo.com')))
        ->toBeInstanceOf(Laraveltoolkit\Sitemap\Sitemap::class)
        ->and($closureRepositories)
        ->toHaveCount(1);
});

it('test that combine index and url in same sitemap will fail', function () {
    $property = (new ReflectionClass(Sitemap::getFacadeRoot()::class))
        ->getProperty('items');
    expect(Sitemap::addIndex('foo'))
        ->toBeInstanceOf(Laraveltoolkit\Sitemap\Sitemap::class)
        ->and(fn () => Sitemap::addUrl('foo.com'))
        ->toThrow('You cannot combine indexes and url in same sitemap.')
        ->and(fn () => Sitemap::fromCollection(collect(), fn () => Sitemap::addUrl('foo.com')))
        ->toThrow('You cannot combine indexes and url in same sitemap.')
        ->and(fn () => Sitemap::fromQuery(User::query(), fn () => Sitemap::addUrl('foo.com')))
        ->toThrow('You cannot combine indexes and url in same sitemap.');

    $property->setValue(Sitemap::getFacadeRoot(), collect());
    expect(Sitemap::addUrl('https://google.com.br'))
        ->toBeInstanceOf(Laraveltoolkit\Sitemap\Sitemap::class)
        ->and(fn () => Sitemap::addIndex('foo'))
        ->toThrow('You cannot combine indexes and url in same sitemap.');

});

it('can registry domain items', function () {
    /** @var Collection $items */
    $domains = (new ReflectionClass(Sitemap::getFacadeRoot()::class))
        ->getProperty('domains')
        ->getValue(Sitemap::getFacadeRoot());

    expect($domains)
        ->toHaveCount(0)
        ->and(Sitemap::domain('foo.bar.test', fn () => Sitemap::addIndex('abc')))
        ->toBeNull()
        ->and($domains)
        ->toHaveCount(1)
        ->and(fn () => Sitemap::domain('foo.bar.test', fn () => Sitemap::addIndex('abc')))
        ->toThrow("Domain 'foo.bar.test' already declared.")
        ->and(Sitemap::domainExists('foo.bar.test'))
        ->toBeTrue()
        ->and(Sitemap::domainExists('foo.bar.test2'))
        ->toBeFalse();
});

it('can\'t add domain inside domain', function () {
    (new ReflectionClass(Sitemap::getFacadeRoot()::class))
        ->getProperty('locked')
        ->setValue(Sitemap::getFacadeRoot(), true);

    expect(fn () => Sitemap::domain('foo.bar.test', fn () => Sitemap::addIndex('abc')))
        ->toThrow('You cannot put a domain inside another domain.');
});

it('can registry index items', function () {
    /** @var Collection $indexes */
    $indexes = (new ReflectionClass(Sitemap::getFacadeRoot()::class))
        ->getProperty('indexes')
        ->getValue(Sitemap::getFacadeRoot());

    expect($indexes)
        ->toHaveCount(0)
        ->and(Sitemap::index('foo', fn () => Sitemap::addIndex('abc')))
        ->toBeNull()
        ->and($indexes)
        ->toHaveCount(1)
        ->and(fn () => Sitemap::index('foo', fn () => Sitemap::addIndex('abc')))
        ->toThrow("Group 'foo' already declared.")
        ->and(Sitemap::indexExists('foo'))
        ->toBeTrue()
        ->and(Sitemap::indexExists('foo2'))
        ->toBeFalse()
        ->and(Sitemap::indexExists(null))
        ->toBeTrue();
});

it('can\'t add group inside group', function () {
    (new ReflectionClass(Sitemap::getFacadeRoot()::class))
        ->getProperty('locked')
        ->setValue(Sitemap::getFacadeRoot(), true);

    expect(fn () => Sitemap::index('foo', fn () => Sitemap::addIndex('abc')))
        ->toThrow('Groups must be placed on root of sitemap.php');
});

it('can process a index', function () {
    Sitemap::index('foo', function () {
        Sitemap::addUrl('https://google.com.br');
        Sitemap::addUrl('https://google.com.br/2');
        Sitemap::fromCollection([3, 4, 5], function (int $num) {
            Sitemap::addUrl('https://google.com.br/'.$num);
        });
    });

    expect(Sitemap::process('foo.test', 'foo'))
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(5);
});

it('can process a domain', function () {
    Sitemap::domain('foo.test', function () {
        Sitemap::addUrl('https://google.com.br');
        Sitemap::addUrl('https://google.com.br/2');
        Sitemap::fromCollection([3, 4, 5], function (int $num) {
            Sitemap::addUrl('https://google.com.br/'.$num);
        });
    });

    expect(Sitemap::process('foo.test', null))
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(5);
});

it('can process default', function () {
    Sitemap::addUrl('https://google.com.br');
    Sitemap::addUrl('https://google.com.br/2');
    Sitemap::fromCollection([3, 4, 5], function (int $num) {
        Sitemap::addUrl('https://google.com.br/'.$num);
    });

    expect(Sitemap::process('foo.test', null))
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(5);
});
