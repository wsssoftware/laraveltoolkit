<?php

use Illuminate\Support\Collection;
use Laraveltoolkit\Actions\Sitemap\RenderSitemap;
use Laraveltoolkit\Facades\Sitemap;
use Laraveltoolkit\Sitemap\Url;

it('can render', function () {
    config()->set('laraveltoolkit.sitemap.timeout', 120);
    $this->get(route('lt.sitemap'))
        ->assertSuccessful();
    $lastModified = filemtime(base_path('routes/sitemap.php'));
    $cacheKey = 'lt.sitemap.'.sha1('localhost'.'::::'.$lastModified);
    expect(Cache::has($cacheKey))->toBeTrue();
});

it('can render without cache', function () {
    config()->set('laraveltoolkit.sitemap.cache', false);
    $this->get(route('lt.sitemap'))
        ->assertSuccessful();
    $lastModified = filemtime(base_path('routes/sitemap.php'));
    $cacheKey = 'lt.sitemap.'.sha1('localhost'.'::::'.$lastModified);
    expect(Cache::has($cacheKey))->toBeFalse();
});

it('log on large files', function () {
    config()->set('laraveltoolkit.sitemap.max_file_size', 1024);
    $rs = new RenderSitemap;
    $content = str_repeat('1', 5_000);
    $url = new Url($content);
    Sitemap::addUrl($url);

    Log::shouldReceive('warning')
        ->andThrow(Exception::class, 'large files');

    expect(fn () => $rs(request()))
        ->toThrow('large files');
});

it('log on large items count', function () {
    config()->set('laraveltoolkit.sitemap.max_file_items', 100);
    /** @var Collection $items */
    $items = (new ReflectionClass(Sitemap::getFacadeRoot()::class))
        ->getProperty('items')
        ->getValue(Sitemap::getFacadeRoot());
    $rs = new RenderSitemap;
    for ($i = 1; $i <= 301; $i++) {
        $items->push(new Url("loc $i"));
    }
    Log::shouldReceive('warning')
        ->andThrow(Exception::class, 'large count');

    expect(fn () => $rs(request()))
        ->toThrow('large count');
});
