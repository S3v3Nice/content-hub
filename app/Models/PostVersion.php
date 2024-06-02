<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Storage;

/**
 * App\Models\PostVersion
 *
 * @property int $id
 * @property int|null $author_id
 * @property int|null $assigned_moderator_id
 * @property int|null $post_id
 * @property int $category_id
 * @property string $cover
 * @property string $title
 * @property string $description
 * @property string $content
 * @property \App\Models\PostVersionStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostVersionAction> $actions
 * @property-read int|null $actions_count
 * @property-read \App\Models\User|null $assignedModerator
 * @property-read \App\Models\User|null $author
 * @property-read \App\Models\PostCategory $category
 * @property-read string $cover_url
 * @property-read \App\Models\Post|null $post
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereAssignedModeratorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereCover($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PostVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'cover',
        'title',
        'description',
        'content',
        'status',
    ];

    protected $hidden = [
        'assigned_moderator_id'
    ];

    protected $casts = [
        'status' => PostVersionStatus::class,
    ];

    protected $appends = [
        'cover_url',
    ];

    public function getCoverUrlAttribute(): string
    {
        return url(Storage::url($this->cover));
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function assignedModerator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_moderator_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(PostVersionAction::class, 'version_id');
    }
}
