<?php

declare(strict_types=1);

namespace Support\Collection\Traits;

use Illuminate\Support\Collection;
use stdClass;

trait ConvertCollectionStdClassToArray
{
    /**
     * @param Collection<stdClass> $collection
     */
    private function toArray(Collection $collection): array
    {
        return $collection
            ->map(fn (stdClass $stdClass) => (array) $stdClass)
            ->toArray();
    }

    private function stdClassToArray(stdClass $stdClass): array
    {
        return (array) $stdClass;
    }
}
