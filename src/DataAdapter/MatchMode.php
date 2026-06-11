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
        if ($this->usesTextSearch()) {
            $builder->whereRaw(
                sprintf(
                    'LOWER(CAST(%s AS %s)) %s ?',
                    $builder->getGrammar()->wrap($column),
                    $this->stringCastType($builder),
                    $this->operator($value),
                ),
                [mb_strtolower($this->value($value))],
                $boolean
            );

            return;
        }

        $builder->where($column, $this->operator($value), $this->value($value), $boolean);
    }

    public function applyGlobal(EloquentBuilder $builder, ?array $columns, mixed $value): void
    {
        $columns ??= collect(Schema::getColumns($builder->getModel()->getTable()))
            ->map(fn (array $column) => $column['name'])
            ->toArray();
        $builder->whereNested(function (QueryBuilder $query) use ($columns, $value) {
            foreach ($columns as $column) {
                $this->apply($query, $column, $value, 'or');
            }
        });
    }

    protected function usesTextSearch(): bool
    {
        return in_array($this, [
            self::STARTS_WITH,
            self::CONTAINS,
            self::NOT_CONTAINS,
            self::ENDS_WITH,
        ], true);
    }

    protected function stringCastType(QueryBuilder $builder): string
    {
        return match ($builder->getConnection()->getDriverName()) {
            'mariadb', 'mysql' => 'CHAR',
            default => 'TEXT',
        };
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
