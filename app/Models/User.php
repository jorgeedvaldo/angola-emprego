<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    public function canAccessFilament(): bool
    {
        return $this->is_admin;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'sex',
        'birth_date',
        'mobile',
        'is_admin',
        'cv_path',
        'subscription_plan',
        'subscription_start',
        'subscription_end',
        'subscription_status',
        'google_id',
        'avatar',
        'bio',
    ];

    /**
     * Generate a unique username from a user's name.
     *
     * Uses Str::slug with '.' as separator. If the slug already exists,
     * appends '.{id}' to ensure uniqueness.
     *
     * @param string $name The user's full name
     * @param int|null $id The user's ID (used for deduplication)
     * @return string
     */
    public static function generateUsername(string $name, ?int $id = null): string
    {
        $baseSlug = Str::slug($name, '.');

        // Fallback for names that produce empty slugs (e.g. non-latin)
        if (empty($baseSlug)) {
            $baseSlug = 'user';
        }

        $username = $baseSlug;

        // Check if this username already exists
        $query = self::where('username', $username);
        if ($id) {
            $query->where('id', '!=', $id);
        }

        if ($query->exists()) {
            // Append the user ID or a random number for uniqueness
            $suffix = $id ?? rand(1000, 9999);
            $username = $baseSlug . '.' . $suffix;
        }

        return $username;
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_user')->withTimestamps()->withPivot('completed_at');
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_user')->withTimestamps()->withPivot('completed_at');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_user')->withTimestamps();
    }

    public function cvs()
    {
        return $this->hasMany(Cv::class);
    }

    public function primaryCv()
    {
        return $this->hasOne(Cv::class)->where('is_primary', true);
    }

    public function skills()
    {
        return $this->hasMany(UserSkill::class);
    }

    public function educations()
    {
        return $this->hasMany(UserEducation::class);
    }

    public function experiences()
    {
        return $this->hasMany(UserExperience::class)->orderBy('start_date', 'desc');
    }

    public function languages()
    {
        return $this->hasMany(UserLanguage::class);
    }

    public function getCompletedCoursesAttribute()
    {
        // Get courses based on lessons the user has interacted with
        $userLessonIds = $this->lessons()->pluck('lesson_id');
        
        if ($userLessonIds->isEmpty()) {
            return collect();
        }

        $courseIds = \App\Models\Lesson::whereIn('id', $userLessonIds)->distinct()->pluck('course_id');
        $courses = \App\Models\Course::whereIn('id', $courseIds)->get();

        return $courses->filter(function ($course) {
            $totalLessons = $course->lessons()->count();
            if ($totalLessons === 0) return false;
            
            $completedLessons = $this->lessons()
                ->whereIn('lesson_id', $course->lessons()->pluck('id'))
                ->whereNotNull('completed_at')
                ->count();
                
            return $completedLessons === $totalLessons;
        });
    }

    public function hasActiveSubscription()
    {
        return $this->subscription_status === 'active' && 
               $this->subscription_end && 
               $this->subscription_end->isFuture();
    }

    public function getCvUrlAttribute()
    {
        // Try getting primary cv first, fallback to legacy path
        $primary = $this->primaryCv;
        if ($primary) {
            return asset('storage/' . $primary->path);
        }
        return $this->cv_path ? asset('storage/' . $this->cv_path) : null;
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'subscription_start' => 'date',
        'subscription_end' => 'date',
    ];
}
