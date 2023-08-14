<?php

namespace Xite\Searchable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

trait Searchable
{
    public static function searchQuery(): Builder
    {
        return self::query();
    }

    public function getSearchable(): Collection
    {
        return collect($this->searchable ?? []);
    }

    public function getSearchableRelations(): Collection
    {
        return collect($this->searchableRelations ?? []);
    }


    public function getSearchableCount(): int
    {
        return $this->getSearchable()->count();
    }

    public function getCustomFilters(): Collection
    {
        return collect($this->filters ?? []);
    }

    abstract public function getDisplayName(): string;
}
