<?php

namespace Laraveltoolkit\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Laraveltoolkit\SEO\Image;
use Laraveltoolkit\SEO\RobotRule;

/**
 * @method static string friendlyUrlString(string $string)
 * @method static string getRobotsTxtSitemap()
 * @method static bool isCrawler(string $agent = null)
 * @method static array payload()
 * @method static string robotsTxt()
 * @method static \Laraveltoolkit\SEO\SEO withoutCanonical(bool $propagate = null)
 * @method static \Laraveltoolkit\SEO\SEO withoutDescription(bool $propagate = null)
 * @method static \Laraveltoolkit\SEO\SEO withoutOpenGraphType()
 * @method static \Laraveltoolkit\SEO\SEO withoutOpenGraphTitle()
 * @method static \Laraveltoolkit\SEO\SEO withoutOpenGraphDescription()
 * @method static \Laraveltoolkit\SEO\SEO withoutOpenGraphUrl()
 * @method static \Laraveltoolkit\SEO\SEO withoutOpenGraphImage()
 * @method static \Laraveltoolkit\SEO\SEO withoutPropagation()
 * @method static \Laraveltoolkit\SEO\SEO withoutRobots()
 * @method static \Laraveltoolkit\SEO\SEO withoutRobotsTxtRule(string $userAgent = null)
 * @method static \Laraveltoolkit\SEO\SEO withoutRobotsTxtSitemap()
 * @method static \Laraveltoolkit\SEO\SEO withoutTitle(bool $propagate = null)
 * @method static \Laraveltoolkit\SEO\SEO withoutTwitterCardCreator()
 * @method static \Laraveltoolkit\SEO\SEO withoutTwitterCardSite()
 * @method static \Laraveltoolkit\SEO\SEO withoutTwitterCardTitle()
 * @method static \Laraveltoolkit\SEO\SEO withoutTwitterCardDescription()
 * @method static \Laraveltoolkit\SEO\SEO withoutTwitterCardImage()
 * @method static \Laraveltoolkit\SEO\SEO withCanonical(string $canonical, bool $propagate = null)
 * @method static \Laraveltoolkit\SEO\SEO withDescription(string $description, bool $propagate = null)
 * @method static \Laraveltoolkit\SEO\SEO withOpenGraphType(string $type)
 * @method static \Laraveltoolkit\SEO\SEO withOpenGraphTitle(string $title)
 * @method static \Laraveltoolkit\SEO\SEO withOpenGraphDescription(string $description)
 * @method static \Laraveltoolkit\SEO\SEO withOpenGraphUrl(string $url)
 * @method static \Laraveltoolkit\SEO\SEO withOpenGraphImage(Image $image)
 * @method static \Laraveltoolkit\SEO\SEO withPropagation()
 * @method static \Laraveltoolkit\SEO\SEO withRobots(RobotRule|string ...$items)
 * @method static \Laraveltoolkit\SEO\SEO withRobotsTxtRule(string $userAgent = null, Collection $allow = null, Collection $disallow = null)
 * @method static \Laraveltoolkit\SEO\SEO withRobotsTxtSitemap(string $url)
 * @method static \Laraveltoolkit\SEO\SEO withTitle(string $title, bool $propagate = null)
 * @method static \Laraveltoolkit\SEO\SEO withTwitterCardSite(string $site)
 * @method static \Laraveltoolkit\SEO\SEO withTwitterCardCreator(string $creator)
 * @method static \Laraveltoolkit\SEO\SEO withTwitterCardTitle(string $title)
 * @method static \Laraveltoolkit\SEO\SEO withTwitterCardDescription(string $description)
 * @method static \Laraveltoolkit\SEO\SEO withTwitterCardImage(Image $image)
 *
 * @see \Laraveltoolkit\SEO\SEO
 */
class SEO extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Laraveltoolkit\SEO\SEO::class;
    }
}
