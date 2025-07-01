<?php

namespace Laraveltoolkit\SEO;

use Illuminate\Support\Arr;

class TwitterCard
{
    public ?string $site;

    public ?string $creator;

    public ?string $title;

    public ?string $description;

    public ?Image $image;

    public function __construct(
        ?string $title = null,
        ?string $description = null,
    ) {
        $this->site = config('laraveltoolkit.seo.defaults.twitter_card.site');
        $this->creator = config('laraveltoolkit.seo.defaults.twitter_card.creator');
        $this->title = config('laraveltoolkit.seo.defaults.twitter_card.title', $title);
        $this->description = config('laraveltoolkit.seo.defaults.twitter_card.description', $description);
        $imageConfig = config('laraveltoolkit.seo.defaults.twitter_card.image',
            ['disk' => null, 'path' => null, 'alt' => null]);
        if (! empty($imageConfig['disk']) && ! empty($imageConfig['path'])) {
            $this->image = new Image(
                Arr::get($imageConfig, 'disk'),
                Arr::get($imageConfig, 'path'),
                Arr::get($imageConfig, 'alt'),
            );
        }
    }
}
