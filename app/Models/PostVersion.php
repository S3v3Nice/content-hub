<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PostVersion
 *
 * @property int $id
 * @property int $submitter_id
 * @property int|null $assigned_moderator_id
 * @property int|null $post_id
 * @property int $category_id
 * @property string $preview
 * @property string $title
 * @property string $description
 * @property string $content
 * @property \App\Models\PostVersionStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereAssignedModeratorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion wherePreview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereSubmitterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PostVersion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PostVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'preview',
        'title',
        'description',
        'content',
        'status',
    ];

    protected $casts = [
        'status' => PostVersionStatus::class,
    ];
}
