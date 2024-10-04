<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Screenshots extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'screenshots';

    protected $fillable = [
        'game_id',
        'path',
    ];
}
