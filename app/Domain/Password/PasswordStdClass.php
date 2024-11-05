<?php

declare(strict_types=1);

namespace Domain\Password;

use Illuminate\Support\Carbon;
use stdClass;

/**
 * @property-read int $id
 * @property-read int $count
 * @property-read string $login
 * @property-read string $resource
 * @property-read string $hash
 * @property-read string|null $password
 * @property-read Carbon $created_at
 * @property-read Carbon $updates_at
 */
class PasswordStdClass extends stdClass
{
}
