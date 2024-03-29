<?php

namespace Xite\Searchable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\Filters\Filter;

class TrimmedNumberFilter implements Filter
{
    public function __invoke(Builder $query, $value = null, $property = null): Builder
    {
        $table = $query->getModel()->getTable();

        $value = Str::of($value)
            ->trim()
            ->replaceMatches('/\W/', '')
            ->upper();

        if ($value->isEmpty()) {
            return $query;
        }

        return $query->where($table . '.' . $property, 'LIKE', $value->prepend('%')->append('%')->toString());
    }
}
