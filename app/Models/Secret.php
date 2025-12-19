<?php
// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;

// class Secret extends Model
// {
//     protected $fillable = [
//         'text', 'slug', 'expires_at', 'used', 'user_id'
//     ];

//     protected $casts = [
//         'expires_at' => 'datetime',
//         'used' => 'boolean',
//     ];
// }



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Secret extends Model
{
    /**
     * Encrypt text before saving to database.
     */
    public function setTextAttribute($value)
    {
        $this->attributes['text'] = encrypt($value);
    }

    /**
     * Decrypt text when accessing from model.
     */
    public function getTextAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value; // fallback jika gagal dekripsi
        }
    }
    use HasFactory;

    protected $fillable = [
        'text',
        'slug',
        'expires_at',
        'used',
        'viewed_at',
        'user_id',
        'one_time',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'viewed_at' => 'datetime',
        'used' => 'boolean',
        'one_time' => 'boolean',
    ];

    protected $hidden = [
        'text', // Hide secret text by default for security
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleting(function ($secret) {
            if ($secret->file_path && \Illuminate\Support\Facades\Storage::exists($secret->file_path)) {
                \Illuminate\Support\Facades\Storage::delete($secret->file_path);
            }
        });
    }

    /**
     * Get the user that owns the secret.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the secret is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the secret is still active (not used and not expired).
     */
    public function isActive(): bool
    {
        return !$this->used && !$this->isExpired();
    }

    /**
     * Get the status of the secret.
     */
    public function getStatusAttribute(): string
    {
        if ($this->used) {
            return 'used';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Scope for active secrets.
     */
    public function scopeActive($query)
    {
        return $query->where('used', false)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for expired secrets.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope for used secrets.
     */
    public function scopeUsed($query)
    {
        return $query->where('used', true);
    }

    /**
     * Scope to get secrets by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
