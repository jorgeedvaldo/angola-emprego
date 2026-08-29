<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'attachment_path',
        'attachment_name',
        'status',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(JobApplicationAttachment::class);
    }

    public function attachmentList()
    {
        $files = $this->relationLoaded('files') ? $this->files : $this->files()->get();

        if ($files->isNotEmpty()) {
            return $files;
        }

        if ($this->attachment_path) {
            return collect([
                new JobApplicationAttachment([
                    'path' => $this->attachment_path,
                    'original_name' => $this->attachment_name,
                ]),
            ]);
        }

        return collect();
    }
}
