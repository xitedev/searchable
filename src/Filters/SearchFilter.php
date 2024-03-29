<?php

namespace Xite\Searchable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SearchFilter
{
    public function __construct(
        private readonly ?string $value = null,
        private readonly bool $strict = false,
        private readonly array $exact = []
    ) {
    }

    public function __invoke(Builder $query): Builder
    {
        if (is_null($this->value) || $this->value === '') {
            return $query;
        }

        return $query->where(
            fn (Builder $query) => $query
                ->tap(fn (Builder $query) => $this->getSearchQuery($query))
                ->tap(fn (Builder $query) => $this->getSearchableRelationsQuery($query))
                ->tap(fn (Builder $query) => $this->getKeySearchQuery($query))
        );
    }

    private function getSearchQuery(Builder $query): Builder
    {
        $model = $query->getModel();

        if (! method_exists($model, 'getSearchable')) {
            return $query;
        }

        if (! $model->getSearchableCount()) {
            return $query;
        }

        return $query->when(
            $this->strict,
            fn (Builder $query) => $this->getStrictSearchQuery($query),
            fn (Builder $query) => $this->getLikeSearchQuery($query)
        );
    }

    private function getStrictSearchQuery(Builder $query): Builder
    {
        $model = $query->getModel();
        $table = $model->getTable();

        $searchable = count($this->exact)
            ? $model->getSearchable()->intersect($this->exact)
            : $model->getSearchable();

        return $query->where(
            fn (Builder $query) => $searchable->each(
                fn (string $field) => $query->when(
                    method_exists($model, 'isTranslatableAttribute') && $model->isTranslatableAttribute($field),
                    fn (Builder $query) => collect(config('app.locales'))
                        ->keys()
                        ->each(fn ($locale) => $query->orWhere($field . '->' . $locale, $this->value)),
                    fn (Builder $query) => $query->orWhere($table . '.' . $field, 'LIKE', $this->value)
                )
            )
        );
    }

    private function getLikeSearchQuery(Builder $query): Builder
    {
        $model = $query->getModel();
        $table = $model->getTable();
        $filters = $model->getCustomFilters();

        $searchable = count($this->exact)
            ? $model->getSearchable()->intersect($this->exact)
            : $model->getSearchable();

        return $query->where(
            fn (Builder $query) => $searchable->each(
                fn ($field) => $query->when(
                    $filter = $filters->get($field),
                    fn (Builder $query) => $query->orWhere(
                        fn (Builder $query) => (new $filter)($query, $this->value, $field)
                    ),
                    fn (Builder $query) => $query->orWhere(
                        fn (Builder $query) => Str::of($this->value)
                            ->explode(' ')
                            ->map(fn ($word) => $this->prepareRawValue($word))
                            ->each(fn ($word) => $query->whereRaw('LOWER(' . $table . '.' . $field . ') LIKE ' . $word))
                    )
                )
            )
        );
    }

    private function prepareRawValue($word): string
    {
        return Str::of($word)
            ->lower()
            ->prepend('"%')
            ->append('%"')
            ->toString();
    }

    private function getSearchableRelationsQuery(Builder $query): Builder
    {
        $model = $query->getModel();

        if (! method_exists($model, 'getSearchableRelations')) {
            return $query;
        }

        $relations = $model
            ->getSearchableRelations()
            ->when(
                count($this->exact),
                fn ($items) => $items->filter(fn ($item) => in_array($item, $this->exact))
            )
            ->mapWithKeys(function ($relation) {
                if (! Str::of($relation)->contains(':')) {
                    return [$relation => []];
                }

                [$key, $values] = explode(':', $relation, 2);

                return [$key => explode(',', $values)];
            })
            ->filter(fn ($exact, $relation) => $model->isRelation($relation));

        return $query->orWhere(
            fn (Builder $query) => $relations
                ->each(
                    fn ($exact, $relation) => $query->orWhereHas(
                        $relation,
                        fn (Builder $query) => $query->tap(new self($this->value, $this->strict, $exact))
                    )
                )
        );
    }

    private function getKeySearchQuery(Builder $query): Builder
    {
        $model = $query->getModel();
        $table = $model->getTable();

        if (! $model->incrementing) {
            return $query;
        }

        $keyName = $model->getKeyName();

        if (! $keyName) {
            return $query;
        }

        if (count($this->exact) && ! in_array($keyName, $this->exact)) {
            return $query;
        }

        return $query->orWhere(
            fn (Builder $query) => Str::of($this->value)
                ->explode(',')
                ->filter(fn ($value) => is_numeric($value))
                ->each(fn ($value) => $query->orWhere($table . '.' . $keyName, $value))
        );
    }
}
