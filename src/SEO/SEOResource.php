<?php

namespace Laraveltoolkit\SEO;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Laraveltoolkit\SEO\Payload $resource
 */
class SEOResource extends JsonResource
{
    public function __construct($resource)
    {
        self::withoutWrapping();
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->when(!empty($this->resource->title), $this->resource->title),
            'description' => $this->when(!empty($this->resource->description), $this->resource->description),
            'canonical' => $this->when(!empty($this->resource->canonical), $this->resource->canonical),
            'robots' => $this->when($this->resource->robots->isNotEmpty(), fn() => $this->robots()),
            'open_graph' => $this->when(
                !empty($this->resource->openGraph->title) && !empty($this->resource->openGraph->type),
                fn() => OpenGraphResource::make($this->resource->openGraph)
            ),
            'twitter_card' => $this->when(
                !empty($this->resource->twitterCard->title),
                fn() => TwitterCardResource::make($this->resource->twitterCard)
            ),
        ];
    }

    protected function robots(): string
    {
        return $this->resource->robots
            ->map(fn(array $item) => $item[0]->value.(!empty($item[1]) ? ':'.$item[1] : ''))
            ->implode(',');
    }
}
