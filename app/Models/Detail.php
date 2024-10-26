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
        return $this->belongsToMany(Categories::class, 'film_categories_link',
            'film_id', 'category_id');
    }

    public function comments()
    {
        return $this->hasMany(Comments::class, 'film_id', 'id');
    }

    public function film()
    {
        return $this->belongsTo(Film::class, 'id', 'id');
    }

    public function screenshots()
    {
        return $this->hasMany(Screenshots::class, 'film_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'film_id', 'id');
    }
}
