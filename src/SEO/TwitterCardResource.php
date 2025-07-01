<?php

namespace Laraveltoolkit\SEO;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Laraveltoolkit\SEO\TwitterCard $resource
 */
class TwitterCardResource extends JsonResource
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
            'card' => !empty($this->resource->image) ? 'summary_large_image' : 'summary',
            'site' => $this->when(!empty($this->resource->site), $this->resource->site),
            'creator' => $this->when(!empty($this->resource->creator), $this->resource->creator),
            'title' => $this->resource->title,
            'description' => $this->when(!empty($this->resource->description), $this->resource->description),
            'image' => $this->when(!empty($this->resource->image), fn() => ImageResource::make($this->resource->image)),
        ];
    }
}
