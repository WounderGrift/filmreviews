<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banners extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'banners';

    protected $fillable = [
        'banner_path',
        'banner_name',
        'type',
        'media_type',
        'position',
        'href',
        'active'
    ];

    public function bannersStatistics()
    {
        return $this->hasMany(BannerStatistics::class, 'banner_id', 'id');
    }
}
