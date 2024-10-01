<?php

namespace Support\Collection\Traits;

use Illuminate\Support\Collection;
use stdClass;

trait ConvertCollectionStdClassToArray
{
    /**
     * @param Collection<stdClass> $collection
     * @return array
     */
    private function toArray(Collection $collection): array
    {
        return $collection
            ->map(fn(stdClass $stdClass) => (array) $stdClass)
            ->toArray();
    }

    private function stdClassToArray(stdClass $stdClass): array
    {
        return (array) $stdClass;
    }
}
