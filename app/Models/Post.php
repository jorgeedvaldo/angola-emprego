<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'image'
    ];

	protected static function boot()
    {
        parent::boot();

        static::created(function ($post) {
            $post->slug = $post->generateSlug($post->title, $post->id);
            $post->save();

            $post->generateThumb($post->image);
        });

        static::updated(function ($post) {
            $post->generateThumb($post->image);
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

    private function generateThumb($url)
    {
        // create image manager with desired driver
        $manager = new ImageManager(new Driver());

        // read image from file system
        $image = $manager->read('storage/' . $url);

        // resize image proportionally to 300px width
        $image->cover(500, 300);

        // save modified image in new format
        $image->save('storage/thumb/' . $url);
    }

    public static function getCachedLatest()
    {
        return Cache::remember('latest_posts_50', 60, function () {
            return self::orderByRaw('id DESC')->limit(50)->get();
        });
    }
}
