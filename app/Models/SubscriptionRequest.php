<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'plan', 'status'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::updated(function ($request) {
            if ($request->isDirty('status') && $request->status === 'approved') {
                $duration = match ($request->plan) {
                    'weekly' => 7,
                    'monthly' => 30,
                    'quarterly' => 90,
                    'yearly' => 365,
                    default => 0,
                };

                if ($duration > 0) {
                    $request->user->update([
                        'subscription_plan' => $request->plan,
                        'subscription_start' => now(),
                        'subscription_end' => now()->addDays($duration),
                        'subscription_status' => 'active',
                    ]);
                }
            }
        });
    }
}
