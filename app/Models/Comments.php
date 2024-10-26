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
        'film_id',
        'comment',
    ];

    public function detail()
    {
        return $this->belongsTo(Detail::class, 'film_id', 'id');
    }

    public function film()
    {
        return $this->belongsTo(Film::class, 'film_id', 'id')->withTrashed();
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
