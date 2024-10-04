<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory;
    use SoftDeletes;

    const STATUS_PUBLISHED   = 'published';
    const STATUS_UNPUBLISHED = 'unpublished';

    protected $table = 'game';

    protected $fillable = [
        'name',
        'uri',
        'series_id',
        'preview_grid',
        'date_release',
        'is_russian_lang',
        'is_weak_pc',
        'is_soft',
        'is_waiting',
        'status',
        'is_sponsor'
    ];

    public function detail()
    {
        return $this->hasOne(Detail::class, 'id', 'id');
    }

    public function torrents()
    {
        return $this->hasMany(Torrents::class, 'game_id');
    }

    public function harvester()
    {
        return $this->hasMany(Harvester::class, 'game_id');
    }

    public function repacks()
    {
        return $this->belongsToMany(Repacks::class, Torrents::class, 'game_id', 'repack_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'games_categories_link',
            'game_id', 'category_id');
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'game_id');
    }

    public function likes()
    {
        return $this->hasMany(Likes::class, 'game_id')
            ->whereNull('comment_id');
    }

    public function series()
    {
        return $this->belongsTo(Series::class, 'series_id');
    }

    public function likesComments()
    {
        return $this->hasMany(Likes::class, 'game_id')
            ->whereNotNull('comment_id');
    }

    public function newsletters()
    {
        return $this->hasMany(Newsletter::class, 'game_id');
    }

    public function comments()
    {
        return $this->hasMany(Comments::class);
    }

    public function trashedScreenshots()
    {
        return $this->hasMany(Screenshots::class, 'game_id', 'id')->onlyTrashed();
    }

    public function trashedTorrents()
    {
        return $this->hasMany(Torrents::class, 'game_id', 'id')->onlyTrashed();
    }
}
