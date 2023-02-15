<?php

namespace Xite\Searchable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class MultipleFilter implements Filter
{
    public function __invoke(Builder $query, $value = null, $property = null): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->when(
                count($value) > 0,
                fn (Builder $query) => $query->whereIn($table . '.' . $property, $value)
            );
    }
}
