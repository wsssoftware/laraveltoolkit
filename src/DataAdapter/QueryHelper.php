<?php

namespace Laraveltoolkit\DataAdapter;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

readonly class QueryHelper
{
    protected array $options;

    public function __construct(
        protected Request $request,
        protected string $pageName,
        protected ?array $globalFilterColumns,
    ) {
        $this->options = $this->request->post("$this->pageName-options", []);
        //
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->options, $key, $default);
    }

    public function rows(): int
    {
        return intval($this->get('rows'));
    }

    public function filters(EloquentBuilder $builder): void
    {
        if (($filters = Filter::create($this->get('filters'), $this->get('global_filter_name'), $builder)) === null) {
            return;
        }
        if (($filter = $filters->where('global', true)->first()) !== null) {
            $filter->applyGlobal($builder, $this->globalFilterColumns);
            $filters = $filters->where('global', false);
        }
        $builder->whereNested(fn (QueryBuilder $q) => $filters->each(fn (Filter $f) => $f->apply($q)));
    }

    public function sort(EloquentBuilder $builder): void
    {
        if (($sort = $this->get('sort')) === null) {
            return;
        }
        $builder->reorder();
        collect(explode(',', $sort))
            ->map(fn (string $item) => explode(':', $item))
            ->mapWithKeys(fn (array $item) => [$item[0] => $item[1]])
            ->each(function (string $dir, string $column) use ($builder) {
                $builder->orderBy($column, $dir);
            });
    }
}
