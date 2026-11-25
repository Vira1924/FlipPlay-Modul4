<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'type',
        'genre',
        'description',
        'episodes',
        'format',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'episodes' => 'integer',
        ];
    }

    /**
     * Users who have added this media to their collection.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Filter media by partial title match.
     */
    public function scopeSearchByTitle(Builder $query, ?string $keyword): Builder
    {
        if ($keyword === null || trim($keyword) === '') {
            return $query;
        }

        return $query->where('title', 'like', '%'.trim($keyword).'%');
    }

    /**
     * Filter media by type.
     */
    public function scopeByType(Builder $query, ?string $type): Builder
    {
        if ($type === null || trim($type) === '') {
            return $query;
        }

        return $query->where('type', trim($type));
    }
}
