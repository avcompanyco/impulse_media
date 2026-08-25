<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Traits\User\HasImageUser;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Cashier\Billable;

use App\Enums\Content\ContentType;
use App\Enums\Content\ContentStatus;
use App\Enums\User\UserStatusEnum;
use App\Enums\User\UserType;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasImageUser, HasRoles;
    use Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'image',
        'bio',
        'external_link',
        'plan_id',
        'status',
        'user_type',
        'accepted_terms_at',
        'payout_method',
        'payout_details',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'image',
    ];

    protected $appends = [
        'image_url',
        'followers_count',
        'followings_count',
        'content_count',
        'is_following',
        'is_followed',
        'is_spectator',
        'is_creator',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatusEnum::class,
            'user_type' => UserType::class,
            'accepted_terms_at' => 'datetime',
        ];
    }


    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the user's current plan based on their active subscription
     */
    public function getCurrentPlan()
    {
        if (env('APP_ENV') == 'production') {
            try {
                if ($this->subscribed('default')) {
                    $subscription = $this->subscription('default');
                    $stripe_product_id = $subscription->asStripeSubscription()->plan->product;
                    return Plan::where('stripe_product_id', $stripe_product_id)->first();
                }
            } catch (\Throwable $th) {
                Log::error('Error getting current plan', ['error' => $th->getMessage()]);
                return $this->plan;
            }
        } 
        
        return $this->plan;
    }

    public function followings()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
    }

    public function follow($userId)
    {
        if ($this->id !== $userId) {
            $this->followings()->syncWithoutDetaching([$userId]);
        }
    }

    public function unfollow($userId)
    {
        $this->followings()->detach($userId);
    }

    public function isFollowing($userId)
    {
        return $this->followings()->where('following_id', $userId)->exists();
    }

    public function isFollowedBy($userId)
    {
        return $this->followers()->where('follower_id', $userId)->exists();
    }

    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }

    public function getFollowingsCountAttribute()
    {
        return $this->followings()->count();
    }

    public function contents()
    {
        return $this->hasMany(Content::class);
    }

    public function movies()
    {
        return $this->contents()->where('type', ContentType::MOVIE->value);
    }

    public function series()
    {
        return $this->contents()->where('type', ContentType::SERIE->value);
    }

    public function shorts()
    {
        return $this->contents()->where('type', ContentType::SHORT->value);
    }

    public function getContentCountAttribute()
    {
        return $this->contents()->where('status', ContentStatus::PUBLISHED->value)->count();
    }

    public function getIsFollowingAttribute()
    {
        if (!Auth::check()) {
            return false;
        }
        return $this->isFollowing(Auth::user()->id);
    }

    public function getIsFollowedAttribute()
    {
        if (!Auth::check()) {
            return false;
        }
        return $this->isFollowedBy(Auth::user()->id);
    }

    /**
     * Check if user is a spectator
     */
    public function isSpectator(): bool
    {
        return $this->user_type === UserType::SPECTATOR;
    }

    /**
     * Check if user is a creator
     */
    public function isCreator(): bool
    {
        return $this->user_type === UserType::CREATOR;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->user_type === UserType::ADMIN || $this->hasRole('admin');
    }

    public function getIsSpectatorAttribute(): bool
    {
        return $this->isSpectator();
    }

    public function getIsCreatorAttribute(): bool
    {
        return $this->isCreator();
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user has reached upload limit for a content type
     */
    public function hasReachedUploadLimit(string $contentType): bool
    {
        $plan = $this->getCurrentPlan();
        if (!$plan) return true;
        if ($plan->hasUnlimitedContent()) return false;

        $currentCount = match ($contentType) {
            'movie' => $this->movies()->count(),
            'serie' => $this->series()->count(),
            'short' => $this->shorts()->count(),
            default => 0,
        };

        $limit = match ($contentType) {
            'movie' => $plan->movies_upload_count,
            'serie' => $plan->series_upload_count,
            'short' => $plan->shorts_upload_count,
            default => 0,
        };

        return $currentCount >= $limit;
    }

    /**
     * Get upload usage for display
     */
    public function getUploadUsage(): array
    {
        $plan = $this->getCurrentPlan();
        if (!$plan) return [];

        $unlimited = $plan->hasUnlimitedContent();

        return [
            'movies' => [
                'used' => $this->movies()->count(),
                'limit' => $unlimited ? '∞' : $plan->movies_upload_count,
                'unlimited' => $unlimited,
            ],
            'series' => [
                'used' => $this->series()->count(),
                'limit' => $unlimited ? '∞' : $plan->series_upload_count,
                'unlimited' => $unlimited,
            ],
            'shorts' => [
                'used' => $this->shorts()->count(),
                'limit' => $unlimited ? '∞' : $plan->shorts_upload_count,
                'unlimited' => $unlimited,
            ],
        ];
    }

    public function makeHiddenStripe()
    {
        $this->makeHidden(['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at', 'subscription_ends_at']);
    }

    /**
     * Get the user's payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the user's binacles (event logs)
     */
    public function binacles()
    {
        return $this->hasMany(Binacle::class);
    }

    public function getTrialDaysEnds()
    {
        if ($this->subscribed('default')) {
            $trial_ends_at = $this->subscription('default')->trial_ends_at;
            return now()->diffInDays($trial_ends_at);
        }
        return null;
    }

    public function getNextPaymentDate()
    {

        $subscription = $this->subscription('default');

        $next_payment_date = " - ";

        try {
            if ($subscription) {
                // Get the Stripe Subscription object
                $stripeSubscription = $subscription->asStripeSubscription();
    
                $nextPaymentTimestamp = $stripeSubscription->current_period_end;
    
                $nextPaymentDate = Carbon::createFromTimestamp($nextPaymentTimestamp);
    
                $next_payment_date = $nextPaymentDate->toFormattedDateString();
            }
        } catch (\Throwable $th) {
            Log::error('Error getting next payment date', ['error' => $th->getMessage()]);
        }


        return $next_payment_date;
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function earnings()
    {
        return $this->hasMany(CreatorEarning::class, 'creator_id');
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class, 'creator_id');
    }

    public function isImpulseMember(): bool
    {
        if ($this->user_type !== UserType::SPECTATOR) {
            return false;
        }
        
        $plan = $this->getCurrentPlan();
        if (!$plan || $plan->plan_type !== 'spectator') {
            return false;
        }

        if (env('APP_ENV') == 'production') {
            return $this->subscribed('default');
        }

        return true; // Local development bypass
    }

    public function getCreatorBalanceAttribute()
    {
        $earned = $this->earnings()->sum('amount');
        $withdrawn = $this->payouts()->whereIn('status', ['approved', 'pending'])->sum('amount');
        return max(0.00, (float)$earned - (float)$withdrawn);
    }

    public function getLifetimeEarningsAttribute()
    {
        return (float) $this->earnings()->sum('amount');
    }
}

