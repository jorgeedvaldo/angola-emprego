<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Http\Middleware\CacheDePaginasPublicas;
use App\Support\Resiliente;

use App\Models\Concerns\GeneratesCoverAndThumbnail;

class Post extends Model
{
    use HasFactory, GeneratesCoverAndThumbnail;

    protected $fillable = [
        'title', 'slug', 'description', 'image'
    ];

	protected static function boot()
    {
        parent::boot();

        static::created(function ($post) {
            $post->slug = $post->generateSlug($post->title, $post->id);
            $post->generateCoverIfMissing('images/posts', 'ARTIGO');
            $post->save();

            $post->generateThumb($post->image);
        });

        static::updated(function ($post) {
            $post->generateThumb($post->image);
        });

        static::saved(function ($post) {
            Resiliente::esquecer('latest_posts_50', 'post_' . $post->slug);
            CacheDePaginasPublicas::invalidar();
        });

        static::deleted(function ($post) {
            Resiliente::esquecer('latest_posts_50', 'post_' . $post->slug);
            CacheDePaginasPublicas::invalidar();
        });
    }

    private function generateSlug($title, $id)
    {
        if (static::whereSlug($slug = Str::slug($title))->exists()) {
            $max = static::whereTitle($title)->latest('id');
            $slug = $slug . '-' . $id;
        }
        return $slug;
    }

    public static function getCachedLatest()
    {
        return Resiliente::lembrar('latest_posts_50', config('resiliencia.normal'), function () {
            return self::orderByRaw('id DESC')->limit(50)->get();
        });
    }
}
