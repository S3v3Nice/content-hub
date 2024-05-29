<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PostCommentLike
 *
 * @property int $id
 * @property int $comment_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PostComment $comment
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|PostCommentLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostCommentLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostCommentLike query()
 * @method static \Illuminate\Database\Eloquent\Builder|PostCommentLike whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostCommentLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostCommentLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostCommentLike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostCommentLike whereUserId($value)
 * @mixin \Eloquent
 */
class PostCommentLike extends Model
{
    use HasFactory;

    public function comment(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'comment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
