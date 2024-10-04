<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'for_soft',
        'label',
        'url',
    ];

    public $timestamps = false;

//    public function categories()
//    {
//        return $this->belongsToMany(Game::class, 'games_repacks_link',
//            'categories_id', 'game_id');
//    }

    public function gamesCategoriesLink()
    {
        return $this->belongsToMany(Game::class, 'games_categories_link',
            'category_id', 'game_id');
    }

    public function hasGamesCategoriesLink()
    {
        return $this->gamesCategoriesLink()->exists();
    }
}
