<?php

namespace Laraveltoolkit\SEO;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Image $resource
 */
class ImageResource extends JsonResource
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
            'alt' => $this->when(! empty($this->resource->alt), $this->resource->alt),
            'url' => $this->resource->toUrl(),
        ];
    }
}
