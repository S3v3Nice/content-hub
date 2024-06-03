<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PostVersionAction
 *
 * @property int $id
 * @property int $version_id
 * @property int|null $user_id
 * @property \App\Models\PostVersionActionType $type
 * @property array $details
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\PostVersion $version
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionAction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionAction query()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionAction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionAction whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionAction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionAction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionAction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionAction whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersionAction whereVersionId($value)
 * @mixin \Eloquent
 */
class PostVersionAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'details',
    ];

    protected $casts = [
        'type' => PostVersionActionType::class,
        'details' => 'array',
    ];

    protected $attributes = [
        'details' => '{}',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(PostVersion::class, 'version_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
