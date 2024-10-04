<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Likes extends Model
{
    use HasFactory;

    protected $table = 'likes';

    protected $fillable = [
        'game_id',
        'user_id',
        'comment_id'
    ];

    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function comments()
    {
        return $this->belongsTo(Comments::class, 'comment_id', 'id');
    }
}
