<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'company', 'location', 'description', 'email_or_link', 'image'
    ];

	protected static function boot()
    {
        parent::boot();

        static::created(function ($job) {
            $job->slug = $job->generateSlug($job->title, $job->id);
            $job->save();

            SocialMediaJob::create([
                'job_id' => $job->id,
                'post_status' => 0,
            ]);
        });

        static::saved(function ($job) {
            Cache::forget('latest_jobs_50');
            Cache::forget('job_' . $job->slug);
        });

        static::deleted(function ($job) {
            Cache::forget('latest_jobs_50');
            Cache::forget('job_' . $job->slug);
        });
    }

    private function generateSlug($title, $id)
    {
        if (static::whereSlug($slug = Str::slug($title))->exists()) {
            $slug = $slug . '-' . $id;
        }
        return $slug;
    }

    public function categories()
    {
        return $this->belongsToMany('App\Models\Category', 'category_jobs');
    }

    public static function getCachedLatest()
    {
        // 1440 minutes = 24 hours
        return Cache::remember('latest_jobs_50', 1440, function () {
            return self::orderByRaw('id DESC')->limit(50)->get();
        });
    }
}
