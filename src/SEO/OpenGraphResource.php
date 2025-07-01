<?php

namespace Laraveltoolkit\SEO;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Laraveltoolkit\SEO\OpenGraph $resource
 */
class OpenGraphResource extends JsonResource
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
            'type' => $this->resource->type,
            'title' => $this->resource->title,
            'description' => $this->when(! empty($this->resource->description), fn () => $this->resource->description),
            'url' => $this->when(! empty($this->resource->url), fn () => $this->resource->url),
            'image' => $this->when(! empty($this->resource->image), fn () => ImageResource::make($this->resource->image)),
        ];
    }
}
