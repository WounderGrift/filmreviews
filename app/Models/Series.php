<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Series extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'series';

    protected $fillable = [
        'name',
        'uri',
        'preview',
        'description',
    ];

    public function games()
    {
        return $this->hasMany(Game::class, 'series_id');
    }
}
