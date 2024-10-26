<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'label',
        'url',
    ];

    public $timestamps = false;

    public function filmsCategoriesLink()
    {
        return $this->belongsToMany(Film::class, 'file_categories_link',
            'category_id', 'file_id');
    }

    public function hasFilmCategoriesLink()
    {
        return $this->filmsCategoriesLink()->exists();
    }
}
