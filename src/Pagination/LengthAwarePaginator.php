<?php

namespace Laraveltoolkit\Pagination;

use Closure;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class LengthAwarePaginator extends \Illuminate\Pagination\LengthAwarePaginator
{
    /**
     * @var Closure|class-string<JsonResource>|null
     */
    protected static Closure|string|null $mapOrResource = null;

    public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
    {
        $mapOrResource = self::$mapOrResource;
        if (! is_null($mapOrResource) && $items instanceof Collection) {
            if ($mapOrResource instanceof Closure) {
                $items->transform($mapOrResource);
            } elseif (class_exists($mapOrResource) && is_subclass_of($mapOrResource, JsonResource::class)) {
                $items = collect($mapOrResource::collection($items)->resolve(request()));
            }
            self::$mapOrResource = null;
        }
        parent::__construct($items, $total, $perPage, $currentPage, $options);
    }

    public static function mapOrResource(Closure|string|null $mapOrResource): void
    {
        static::$mapOrResource = $mapOrResource;
    }

    public function toArray(): array
    {
        return [
            'page_name' => Arr::get($this->options, 'pageName'),
            ...parent::toArray(),
        ];
    }
}
