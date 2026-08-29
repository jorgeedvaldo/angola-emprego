<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'headline',
        'description',
        'logo',
        'website',
        'location',
        'email',
        'phone',
        'linkedin_url',
        'facebook_url',
        'instagram_url',
        'max_attachments',
    ];

    public const MAX_ATTACHMENTS_LIMIT = 10;

    protected $casts = [
        'max_attachments' => 'integer',
    ];

    public function allowedAttachmentCount(): int
    {
        $count = (int) ($this->max_attachments ?: 1);

        return max(1, min(self::MAX_ATTACHMENTS_LIMIT, $count));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/company/' . $this->slug);
    }

    /**
     * Companies without a logo still get their own favicon so the careers page
     * never falls back to the marketplace branding.
     */
    public function faviconUrl(): string
    {
        if ($this->logo) {
            return $this->logo_url;
        }

        $initial = strtoupper(mb_substr($this->name, 0, 1));

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            . '<rect width="64" height="64" rx="14" fill="#2557a7"/>'
            . '<text x="50%" y="50%" dy=".35em" text-anchor="middle" fill="#ffffff"'
            . ' font-family="Arial, Helvetica, sans-serif" font-size="34" font-weight="bold">'
            . e($initial) . '</text></svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'empresa';
        }

        $slug = $base;
        $i = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
