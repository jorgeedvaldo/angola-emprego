<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Http\Middleware\CacheDePaginasPublicas;
use App\Support\Resiliente;

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
            Resiliente::esquecer('categories_all', 'categories_with_jobs');
            CacheDePaginasPublicas::invalidar();
        });

        static::deleted(function ($category) {
            Resiliente::esquecer('categories_all', 'categories_with_jobs');
            CacheDePaginasPublicas::invalidar();
        });
    }

    public static function getCachedAll()
    {
        return Resiliente::lembrar('categories_all', config('resiliencia.normal'), function () {
            return self::orderBy('name')->get();
        });
    }

    public static function getCachedWithJobs()
    {
        return Resiliente::lembrar('categories_with_jobs', config('resiliencia.normal'), function () {
            return self::withCount('jobs')->orderBy('name')->get();
        });
    }
}
