<?php

namespace Laraveltoolkit\SEO;

use Illuminate\Support\Collection;

class Payload
{
    public bool $propagation;

    public ?string $title;

    public ?string $description;

    public null|string|false $canonical;

    public Collection $robots;

    public RobotsTxt $robotsTxt;

    public ?OpenGraph $openGraph;

    public ?TwitterCard $twitterCard;

    public function __construct()
    {
        $this->propagation = boolval(config('laraveltoolkit.seo.propagation', false));
        $this->title = config('laraveltoolkit.seo.defaults.title');
        $this->description = config('laraveltoolkit.seo.defaults.description');
        $this->canonical = config('laraveltoolkit.seo.defaults.canonical');
        $propagated = $this->propagation ? [$this->title, $this->description, $this->canonical] : [null, null, null];
        $this->robotsTxt = new RobotsTxt;
        $this->openGraph = new OpenGraph(...$propagated);
        $this->twitterCard = new TwitterCard(...$propagated);
    }
}
