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
        'cover_image',
        'theme_color',
        'website',
        'location',
        'email',
        'phone',
        'linkedin_url',
        'facebook_url',
        'instagram_url',
        'max_attachments',
        'approval_status',
        'approval_notes',
        'approved_at',
        'approved_by',
    ];

    public const MAX_ATTACHMENTS_LIMIT = 10;

    protected $casts = [
        'max_attachments' => 'integer',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Company $company) {
            if ($company->isDirty('approval_status')) {
                if ($company->approval_status === 'approved') {
                    $company->approved_at = $company->approved_at ?: now();
                    $company->approved_by = $company->approved_by ?: auth()->id();
                } else {
                    $company->approved_at = null;
                    $company->approved_by = null;
                }
            }
        });
    }

    public function allowedAttachmentCount(): int
    {
        $count = (int) ($this->max_attachments ?: 1);

        return max(1, min(self::MAX_ATTACHMENTS_LIMIT, $count));
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isPublic(): bool
    {
        return $this->isApproved() && (bool) $this->user?->hasVerifiedEmail();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }

    public function getThemeColorAttribute(?string $value): string
    {
        return $value && preg_match('/^#[0-9A-Fa-f]{6}$/', $value)
            ? strtoupper($value)
            : '#2557A7';
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
            . '<rect width="64" height="64" rx="14" fill="' . $this->theme_color . '"/>'
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
