<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            if (! $category->slug) {
                $baseSlug = Str::slug($category->name);
                $slug = $baseSlug;
                $counter = 2;

                while (static::query()
                    ->where('slug', $slug)
                    ->whereKeyNot($category->id)
                    ->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $category->slug = $slug;
            }
        });
    }
}
