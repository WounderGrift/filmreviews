<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerStatistics extends Model
{
    use HasFactory;

    protected $table = 'banners_statistics';

    protected $fillable = [
        'banner_id',
        'user_id',
    ];

    const UPDATED_AT = null;
}
