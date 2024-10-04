<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    const ROLE_OWNER      = 'owner';
    const ROLE_ADMIN      = 'admin';
    const ROLE_FREQUENTER = 'frequenter';

    protected array $roleOption = [
      self::ROLE_OWNER => "Владелец",
      self::ROLE_ADMIN => "Админ",
      self::ROLE_FREQUENTER => "Завсегдатый",
    ];

    protected $fillable = [
        'cid',
        'name',
        'email',
        'role',
        'password',
        'avatar_name',
        'avatar_path',
        'status',
        'about_me',
        'timezone',
        'is_verify',
        'get_letter_release',
        'last_activity',
        'is_banned',
    ];

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getRoleOption() {
        return $this->roleOption;
    }

    public function checkAdmin(): bool
    {
        if ($this->id) {
            $isBannedOrNotVerify = $this->is_banned || !$this->is_verify;
            return !($isBannedOrNotVerify || $this->role != self::ROLE_ADMIN);
        }

        return false;
    }

    public function checkOwner(): bool
    {
        if ($this->id) {
            $isBannedOrNotVerify = $this->is_banned || !$this->is_verify;
            return !($isBannedOrNotVerify || $this->role != self::ROLE_OWNER);
        }

        return false;
    }

    public function checkOwnerOrAdmin(): bool
    {
        if ($this->id) {
            $isBannedOrNotVerify = $this->is_banned || !$this->is_verify;
            $isOwnerOrAdmin = $this->role == self::ROLE_OWNER || $this->role == self::ROLE_ADMIN;
            return !($isBannedOrNotVerify || !$isOwnerOrAdmin);
        }

        return false;
    }

    public function setCidAttribute($value)
    {
        if (!$value && empty($this->attributes['cid'])) {
            $this->attributes['cid'] = Str::random(12);
        } else {
            $this->attributes['cid'] = $value;
        }
    }

    public function oneTimeToken()
    {
        return $this->belongsTo(OneTimeToken::class, 'id', 'user_id');
    }

    public function wishlist()
    {
        return $this->belongsToMany(Game::class, 'wishlist', 'user_id', 'game_id');
    }

    public function likes()
    {
        return $this->hasMany(Likes::class, 'user_id', 'id');
    }

    public function likesToComments()
    {
        return $this->hasMany(Likes::class, 'user_id', 'id')
            ->where('comment_id', '!=',null);
    }

    public function likesToGames()
    {
        return $this->hasMany(Likes::class, 'user_id', 'id')
            ->where('comment_id',null);
    }

    public function comments()
    {
        return $this->hasMany(Comments::class, 'from_id', 'id');
    }

    public function newsletters()
    {
        return $this->belongsToMany(Game::class, 'newsletter_for_updates', 'user_id', 'game_id')
            ->withTrashed();
    }

    public function downloadStatistic()
    {
        return $this->hasMany(DownloadStatistics::class, 'user_id', 'id');
    }

    public function bannerStatistic()
    {
        return $this->hasMany(BannerStatistics::class, 'user_id', 'id');
    }
}
