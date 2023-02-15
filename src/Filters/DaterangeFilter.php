<?php

namespace Xite\Searchable\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\Filters\Filter;

class DaterangeFilter implements Filter
{
    public function __invoke(Builder $query, $value = null, $property = null): Builder
    {
        $range = Arr::wrap($value);
        $table = $query->getModel()->getTable();

        return $query
            ->when(
                count($range) === 2,
                fn (Builder $query) => $query->whereBetween(
                    $table . '.' . $property,
                    [
                        Carbon::createFromFormat('Y-m-d', $range[0])->startOfDay(),
                        Carbon::createFromFormat('Y-m-d', $range[1])->endOfDay(),
                    ]
                )
            )
            ->when(
                count($range) === 1,
                fn (Builder $query) => $query->whereBetween(
                    $table . '.' . $property,
                    [
                        Carbon::createFromFormat('Y-m-d', $range[0])->startOfDay(),
                        Carbon::createFromFormat('Y-m-d', $range[0])->endOfDay(),
                    ]
                )
            );
    }
}
