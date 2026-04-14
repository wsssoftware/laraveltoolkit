<?php

namespace Laraveltoolkit\DataAdapter;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @property Collection<int, Constraint> $constraints
 */
readonly class Filter
{
    private function __construct(
        public string $field,
        public Operator $operator,
        public Collection $constraints,
        public bool $global,
    ) {}

    /**
     * @return Collection<string, Filter>|null
     */
    public static function create(?array $filters, string $globalFilterName, EloquentBuilder $builder): ?Collection
    {
        if (empty($filters)) {
            return null;
        }

        $collection = collect($filters)
            ->mapWithKeys(fn (array $filter, string $key) => [
                $key => self::createItem($key, $globalFilterName, $filter, $builder),
            ])
            ->filter(fn ($filter) => $filter instanceof Filter);

        return $collection->count() > 0 ? $collection : null;
    }

    protected static function createItem(
        string $field,
        string $globalFilterName,
        array $data,
        EloquentBuilder $builder
    ): ?Filter {
        $operatorValue = trim(Arr::get($data, 'operator', 'and'));
        $operator = Operator::tryFrom(! empty($operatorValue) ? $operatorValue : 'and');
        $constraints = collect(Arr::get($data, 'constraints', [$data]))
            ->map(fn (array $constraint) => Constraint::create($constraint))
            ->filter(fn ($filter) => $filter instanceof Constraint);
        $valid = $constraints->isNotEmpty() && $operator !== null;

        return $valid
            ? new Filter(self::fieldName($field, $builder), $operator, $constraints, $field === $globalFilterName)
            : null;
    }

    protected static function fieldName(string $field, EloquentBuilder $builder): string
    {
        $query = $builder->getQuery();
        if (count($query?->joins ?? []) === 0) {
            return $field;
        }

        foreach ($query->columns as $column) {
            if ($column instanceof Expression) {
                $column = $column->getValue($query->grammar);
            }
            $column = str($column)
                ->deduplicate()
                ->remove(['(', ')', '`', '\'', '"'], '')
                ->replace([' As ', 'AS', 'aS'], ' as ');
            if ($column->contains(".$field") || $column->contains(" as $field")) {
                $field = $column
                    ->before(' as ')
                    ->toString();
            }
        }

        return $field;
    }

    public function apply(QueryBuilder $builder): void
    {
        $builder->whereNested(fn (QueryBuilder $query) => $this->constraints->each(
            fn (Constraint $constraint) => $constraint->matchMode->apply(
                $query,
                $this->field,
                $constraint->value,
                $this->operator->value
            )
        ));
    }

    public function applyGlobal(EloquentBuilder $builder, ?array $globalFilterColumns): void
    {
        $constraint = $this->constraints->first();
        $constraint->matchMode->applyGlobal($builder, $globalFilterColumns, $constraint->value);
    }
}
