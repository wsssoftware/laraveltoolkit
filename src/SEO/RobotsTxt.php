<?php

namespace Laraveltoolkit\SEO;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class RobotsTxt
{
    /**
     * @var \Illuminate\Support\Collection<string, \Laraveltoolkit\SEO\RobotsTxtRule>
     */
    public Collection $rules;

    public ?string $sitemap;

    public function __construct()
    {
        $this->rules = collect(config('laraveltoolkit.seo.defaults.robots_txt.rules', []))
            ->mapWithKeys(fn($rule) => [
                $rule['user_agent'] => new RobotsTxtRule($rule['user_agent'], $rule['allow'], $rule['disallow']),
            ]);
        $configSitemap = config('laraveltoolkit.seo.defaults.robots_txt.sitemap');
        $this->sitemap = !empty($configSitemap) ? $configSitemap : (Route::has('lt.sitemap') ? route('lt.sitemap') : null);
    }
}
