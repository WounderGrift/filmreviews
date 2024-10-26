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

    public function films()
    {
        return $this->hasMany(Film::class, 'series_id');
    }
}
