<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function jobs()
    {
        return $this->belongsToMany('App\Models\Job', 'category_jobs');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'category_user');
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($category) {
            Cache::forget('categories_all');
            Cache::forget('categories_with_jobs');
        });

        static::deleted(function ($category) {
            Cache::forget('categories_all');
            Cache::forget('categories_with_jobs');
        });
    }

    public static function getCachedAll()
    {
        return Cache::remember('categories_all', 1440, function () {
            return self::orderBy('name')->get();
        });
    }

    public static function getCachedWithJobs()
    {
        return Cache::remember('categories_with_jobs', 1440, function () {
            return self::withCount('jobs')->orderBy('name')->get();
        });
    }
}
