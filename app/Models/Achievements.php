<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievements extends Model
{
    use HasFactory;

    protected $table = 'achievements';

    protected $fillable = [
        'label',
        'background',
    ];

    public function achievements()
    {
        return $this->belongsToMany(Users::class, 'users_achievements_link',
            'achievement_id', 'user_id');
    }
}
