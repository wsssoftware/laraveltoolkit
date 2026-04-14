<?php

namespace Laraveltoolkit\Flash;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Message $resource
 */
class FlashResource extends JsonResource
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
            'id' => $this->resource->id,
            'severity' => $this->resource->severity->value,
            'summary' => $this->when($this->resource->summary !== null, $this->resource->summary),
            'detail' => $this->resource->detail,
            'closable' => $this->when($this->resource->closable !== null, $this->resource->closable),
            'life' => $this->when($this->resource->life !== null, $this->resource->life),
            'group' => $this->when($this->resource->group !== null, $this->resource->group),
        ];
    }
}
