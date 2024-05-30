<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Post
 *
 * @property int $id
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostComment> $comments
 * @property-read int|null $comments_count
 * @property-read int $comment_count
 * @property-read bool $is_liked
 * @property-read int $like_count
 * @property-read \App\Models\PostVersion $version
 * @property-read int $view_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostLike> $likes
 * @property-read int|null $likes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostVersion> $versions
 * @property-read int|null $versions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostView> $views
 * @property-read int|null $views_count
 * @method static \Illuminate\Database\Eloquent\Builder|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post query()
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
    ];

    protected $appends = [
        'version',
        'like_count',
        'comment_count',
        'view_count',
        'is_liked',
    ];

    public function getVersionAttribute(): PostVersion
    {
        return PostVersion::wherePostId($this->id)
            ->whereStatus(PostVersionStatus::Accepted)
            ->orderBy('id', 'desc')
            ->with(['author', 'category'])
            ->first();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PostVersion::class, 'post_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class, 'post_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(PostView::class, 'post_id');
    }

    public function getLikeCountAttribute(): int
    {
        return $this->likes()->count();
    }

    public function getCommentCountAttribute(): int
    {
        return $this->comments()->count();
    }

    public function getViewCountAttribute(): int
    {
        return $this->views()->count();
    }

    public function getIsLikedAttribute(): bool
    {
        $user = Auth::user();
        return $user !== null && $this->likes()->where('user_id', $user->id)->exists();
    }
}
