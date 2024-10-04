<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $table = 'newsletter_for_updates';

    protected $fillable = [
        'user_id',
        'game_id',
        'email',
    ];

    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }
}
