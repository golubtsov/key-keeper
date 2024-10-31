<?php

declare(strict_types=1);

namespace Domain\Password\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $login
 * @property string $resource
 * @property string $hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Password newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Password newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Password query()
 * @method static \Illuminate\Database\Eloquent\Builder|Password whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Password whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Password whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Password whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Password whereResource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Password whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class Password extends Model
{
    protected $fillable = [
        'login',
        'resource',
        'hash',
    ];
}
