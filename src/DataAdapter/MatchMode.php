<?php

namespace Laraveltoolkit\DataAdapter;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Schema;

enum MatchMode: string
{
    case STARTS_WITH = 'startsWith';
    case CONTAINS = 'contains';
    case NOT_CONTAINS = 'notContains';
    case ENDS_WITH = 'endsWith';
    case EQUALS = 'equals';
    case NOT_EQUALS = 'notEquals';

    public function apply(QueryBuilder $builder, string $column, mixed $value, string $boolean): void
    {
        $builder->where($column, $this->operator($value), $this->value($value), $boolean);
    }

    public function applyGlobal(EloquentBuilder $builder, ?array $columns, mixed $value): void
    {
        $columns ??= collect(Schema::getColumns($builder->getModel()->getTable()))
            ->map(fn(array $column) => $column['name'])
            ->toArray();
        $builder->whereNested(function (QueryBuilder $query) use ($columns, $value) {
            foreach ($columns as $column) {
                $query->where($column, $this->operator($value), $this->value($value), 'or');
            }
        });
    }

    protected function operator(mixed $value): string
    {
        return match ($this) {
            self::STARTS_WITH, self::CONTAINS, self::ENDS_WITH => 'LIKE',
            self::NOT_CONTAINS => 'NOT LIKE',
            self::EQUALS => '=',
            self::NOT_EQUALS => '!=',
        };
    }

    protected function value(string $value): string
    {
        $value = trim($value);

        return match ($this) {
            self::STARTS_WITH => "$value%",
            self::CONTAINS, self::NOT_CONTAINS => "%$value%",
            self::ENDS_WITH => "%$value",
            self::EQUALS, self::NOT_EQUALS => $value,
        };
    }
}
