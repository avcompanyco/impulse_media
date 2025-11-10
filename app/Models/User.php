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
use Illuminate\Support\Facades\Auth;

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
        'plan_id',
        'status',
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
            if ($this->subscribed('default')) {
                $subscription = $this->subscription('default');
                $stripe_product_id = $subscription->asStripeSubscription()->plan->product;
                return Plan::where('stripe_product_id', $stripe_product_id)->first();
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

    public function getTrialDaysAttribute()
    {
        if ($this->subscribed('default')) {
            return $this->subscription('default')->trialDays();
        }
        return null;
    }
}
