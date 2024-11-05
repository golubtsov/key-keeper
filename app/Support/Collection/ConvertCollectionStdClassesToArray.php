<?php

declare(strict_types=1);

namespace Support\Collection;

use Illuminate\Support\Collection;
use stdClass;

class ConvertCollectionStdClassesToArray
{
    /**
     * @param Collection<stdClass> $collection
     */
    public function toArray(Collection $collection): array
    {
        return $collection
            ->map(static fn (stdClass $stdClass) => (array) $stdClass)
            ->toArray();
    }
}
