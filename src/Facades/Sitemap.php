<?php

namespace Laraveltoolkit\Facades;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Laraveltoolkit\Sitemap\ChangeFrequency;
use Laraveltoolkit\Sitemap\Index;
use Laraveltoolkit\Sitemap\Url;

/**
 * @method static \Laraveltoolkit\Sitemap\Sitemap addIndex(string $name)
 * @method static \Laraveltoolkit\Sitemap\Sitemap addUrl(Url|string $url, ?Carbon $lastModified = null, ?ChangeFrequency $changeFrequency = null, ?float $priority = null)
 * @method static void domain(string $name, Closure $closure)
 * @method static bool domainExists(string $name)
 * @method static \Laraveltoolkit\Sitemap\Sitemap fromQuery(Builder $builder, Closure $closure, int $count = 1_000)
 * @method static \Laraveltoolkit\Sitemap\Sitemap fromCollection(array|Collection $collection, Closure $closure)
 * @method static void index(string $name, Closure $closure)
 * @method static bool indexExists(string $name)
 * @method static Collection<int, Index|Url> process(string $domain, ?string $index)
 *
 * @see \Laraveltoolkit\Sitemap\Sitemap
 */
class Sitemap extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Laraveltoolkit\Sitemap\Sitemap::class;
    }
}
