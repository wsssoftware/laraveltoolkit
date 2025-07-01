<?php

namespace Laraveltoolkit\Sitemap;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

readonly class ClosureRepository
{
    public function __construct(
        protected Collection|Builder $repository,
        protected Closure $closure,
        protected ?int $count = null,
    ) {
        //
    }

    public function resolve(): void
    {
        if ($this->repository instanceof Collection) {
            $this->repository->each(fn (mixed $item) => $this->closure->call($this, $item));
        } else {
            if (is_int($this->repository->getQuery()->limit)) {
                $this->repository->get()->each(fn (mixed $item) => $this->closure->call($this, $item));
            } else {
                $this->repository->each(
                    fn (mixed $item) => $this->closure->call($this, $item),
                    $this->count ?? 1_000,
                );
            }
        }
    }
}
