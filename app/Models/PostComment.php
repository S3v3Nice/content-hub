<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\PostComment
 *
 * @property int $id
 * @property int $post_id
 * @property int|null $user_id
 * @property int|null $parent_comment_id
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read bool $is_liked
 * @property-read int $like_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostCommentLike> $likes
 * @property-read int|null $likes_count
 * @property-read PostComment|null $parentComment
 * @property-read \App\Models\Post $post
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|PostComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|PostComment whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostComment whereParentCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostComment wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostComment whereUserId($value)
 * @mixin \Eloquent
 */
class PostComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
    ];

    protected $appends = [
        'like_count',
        'is_liked',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'parent_comment_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostCommentLike::class, 'comment_id');
    }

    public function getLikeCountAttribute(): int
    {
        return $this->likes()->count();
    }

    public function getIsLikedAttribute(): bool
    {
        $user = Auth::user();
        return $user !== null && $this->likes()->where('user_id', $user->id)->exists();
    }
}
