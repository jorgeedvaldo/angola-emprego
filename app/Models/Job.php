<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

use App\Models\Concerns\GeneratesCoverAndThumbnail;

class Job extends Model
{
    use HasFactory, GeneratesCoverAndThumbnail;

    protected $fillable = [
        'title', 'slug', 'company', 'location', 'description', 'email_or_link', 'image', 'company_id',
        'description_vector', 'description_vector_model', 'description_vector_generated_at',
    ];

    protected $casts = [
        'description_vector' => 'array',
        'description_vector_generated_at' => 'datetime',
    ];

	protected static function boot()
    {
        parent::boot();

        static::created(function ($job) {
            $job->slug = $job->generateSlug($job->title, $job->id);
            $job->generateCoverIfMissing('images/jobs', 'VAGA');
            $job->save();

            $job->generateThumb($job->image);

            if (\Illuminate\Support\Facades\Schema::hasTable('social_media_jobs')) {
                SocialMediaJob::create([
                    'job_id' => $job->id,
                    'post_status' => 0,
                ]);
            }
        });

        static::updated(function ($job) {
            $job->generateThumb($job->image);
        });

        static::saved(function ($job) {
            Cache::forget('latest_jobs_50');
            Cache::forget('job_' . $job->slug);
            Cache::forget('categories_with_jobs');
        });

        static::deleted(function ($job) {
            Cache::forget('latest_jobs_50');
            Cache::forget('job_' . $job->slug);
            Cache::forget('categories_with_jobs');
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

    public function companyRecord()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function acceptsOnlineApplications(): bool
    {
        return !empty($this->company_id);
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where(function ($visibilityQuery) {
            $visibilityQuery
                ->whereNull('company_id')
                ->orWhereHas('companyRecord', function ($companyQuery) {
                    $companyQuery
                        ->where('approval_status', 'approved')
                        ->whereHas('user', fn ($userQuery) => $userQuery->whereNotNull('email_verified_at'));
                });
        });
    }

    public static function getCachedLatest()
    {
        // 1440 minutes = 24 hours
        return Cache::remember('latest_jobs_50', 1440, function () {
            return self::publiclyVisible()->with('companyRecord')->orderByRaw('id DESC')->limit(50)->get();
        });
    }
}
