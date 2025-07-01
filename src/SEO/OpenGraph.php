<?php

namespace Laraveltoolkit\SEO;

use Illuminate\Support\Arr;

class OpenGraph
{
    public ?string $type;

    public ?string $title;

    public ?string $description;

    public null|string|false $url;

    public ?Image $image;

    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?string $canonical = null,
    ) {
        $this->type = config('laraveltoolkit.seo.defaults.open_graph.type');
        $this->title = config('laraveltoolkit.seo.defaults.open_graph.title', $title);
        $this->description = config('laraveltoolkit.seo.defaults.open_graph.description', $description);
        $this->url = config('laraveltoolkit.seo.defaults.open_graph.url', $canonical);
        $imageConfig = config('laraveltoolkit.seo.defaults.open_graph.image',
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
