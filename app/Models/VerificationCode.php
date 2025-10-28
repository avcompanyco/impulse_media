<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VerificationCode extends Model
{
    /** @use HasFactory<\Database\Factories\VerificationCodeFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'email',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function latestPasswordResetToken()
    {
        return DB::table('password_reset_tokens')
            ->where('email', $this->email)
            ->latest()
            ->first();
    }
}
