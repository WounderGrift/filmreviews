<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comments extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'comments';

    protected $fillable = [
        'from_id',
        'whom_id',
        'game_id',
        'comment',
    ];

    public function detail()
    {
        return $this->belongsTo(Detail::class, 'game_id', 'id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id', 'id')->withTrashed();
    }

    public function user()
    {
        return $this->hasOne(Users::class, 'id', 'from_id');
    }

    public function likes()
    {
        return $this->hasMany(Likes::class, 'comment_id');
    }
}
