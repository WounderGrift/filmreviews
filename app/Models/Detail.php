<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
    use HasFactory;

    protected $table = 'detail';

    protected $fillable = [
        'id',
        'info',
        'preview_detail',
        'preview_trailer',
        'trailer_detail',
    ];

    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'games_categories_link',
            'game_id', 'category_id');
    }

    public function comments()
    {
        return $this->hasMany(Comments::class, 'game_id', 'id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'id', 'id');
    }

    public function screenshots()
    {
        return $this->hasMany(Screenshots::class, 'game_id', 'id');
    }

    public function torrents()
    {
        return $this->hasMany(Torrents::class, 'game_id', 'id');
    }
}
