<?php

namespace Xite\Searchable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait Searchable
{
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

    public static function searchQuery(): Builder
    {
        return self::query();
    }

    abstract public function getDisplayName(): string;
}
