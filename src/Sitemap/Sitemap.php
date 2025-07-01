<?php

namespace Laraveltoolkit\Sitemap;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Sitemap
{
    protected Collection $closureRepositories;

    protected Collection $domains;

    protected Collection $indexes;

    protected bool $locked = false;

    /**
     * @var \Illuminate\Support\Collection<int, Index|Url>
     */
    protected Collection $items;

    public function __construct()
    {
        $this->closureRepositories = collect();
        $this->domains = collect();
        $this->indexes = collect();
        $this->items = collect();
    }

    public function addIndex(string $name): self
    {
        throw_if(
            $this->items->whereInstanceOf(Url::class)->isNotEmpty(),
            Exception::class,
            'You cannot combine indexes and url in same sitemap.'
        );
        $name = trim($name);
        if ($this->items->filter(fn(Index $item) => $item->group === $name)->isEmpty()) {
            $this->items->push(new Index($name));
        }

        return $this;
    }

    public function addUrl(
        Url|string $url,
        ?Carbon $lastModified = null,
        ?ChangeFrequency $changeFrequency = null,
        ?float $priority = null
    ): self {
        throw_if(
            $this->items->whereInstanceOf(Index::class)->isNotEmpty(),
            Exception::class,
            'You cannot combine indexes and url in same sitemap.'
        );
        $url = $url instanceof Url ? $url : new Url($url, $lastModified, $changeFrequency, $priority);
        if ($this->items->filter(fn(Url $item) => $item->loc === $url->loc)->isEmpty()) {
            $this->items->push($url);
        }

        return $this;
    }

    public function domain(string $name, Closure $closure): void
    {
        throw_if($this->locked, Exception::class, 'You cannot put a domain inside another domain.');
        throw_if($this->domains->offsetExists($name), Exception::class, "Domain '{$name}' already declared.");
        $this->domains->put($name, $closure);
    }

    public function domainExists(string $name): bool
    {
        return $this->domains->offsetExists($name);
    }

    public function fromQuery(Builder $builder, Closure $closure, int $count = 1_000): self
    {
        throw_if(
            $this->items->whereInstanceOf(Index::class)->isNotEmpty(),
            Exception::class,
            'You cannot combine indexes and url in same sitemap.'
        );
        $this->closureRepositories->push(new ClosureRepository($builder, $closure, $count));

        return $this;
    }

    public function fromCollection(array|Collection $collection, Closure $closure): self
    {
        throw_if(
            $this->items->whereInstanceOf(Index::class)->isNotEmpty(),
            Exception::class,
            'You cannot combine indexes and url in same sitemap.'
        );
        $this->closureRepositories->push(new ClosureRepository(collect($collection), $closure));

        return $this;
    }

    public function index(string $index, Closure $closure): void
    {
        throw_if($this->locked, Exception::class, 'Groups must be placed on root of sitemap.php');
        throw_if($this->indexes->offsetExists($index), Exception::class, "Group '{$index}' already declared.");
        $this->indexes->put($index, $closure);
    }

    public function indexExists(?string $index): bool
    {
        return $index === null || $this->indexes->offsetExists($index);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Index,Url>
     */
    public function process(string $domain, ?string $index): Collection
    {
        return match (true) {
            !empty($index) => $this->processIndex($index),
            $this->domainExists($domain) => $this->processDomain($domain),
            default => $this->processDefault()
        };
    }

    protected function processIndex(string $name): Collection
    {
        $this->items = collect();
        $this->closureRepositories = collect();
        $this->locked = true;
        $this->indexes->get($name)->call($this);
        $this->resolveClosureRepositories();
        $this->locked = false;

        return $this->items;
    }

    protected function processDomain(string $domain): Collection
    {
        $this->items = collect();
        $this->closureRepositories = collect();
        $this->locked = true;
        $this->domains->get($domain)->call($this);
        $this->resolveClosureRepositories();
        $this->locked = false;

        return $this->items;
    }

    protected function processDefault(): Collection
    {
        $this->resolveClosureRepositories();

        return $this->items;
    }

    protected function resolveClosureRepositories(): void
    {
        $this->closureRepositories->each(fn(ClosureRepository $repository) => $repository->resolve());
    }
}
