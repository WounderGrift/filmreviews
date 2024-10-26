<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Film extends Model
{
    use HasFactory;
    use SoftDeletes;

    const STATUS_PUBLISHED   = 'published';
    const STATUS_UNPUBLISHED = 'unpublished';

    protected $table = 'film';

    protected $fillable = [
        'name',
        'uri',
        'series_id',
        'preview_grid',
        'date_release',
        'is_russian_lang',
        'is_weak_pc',
        'is_waiting',
        'status',
        'is_sponsor'
    ];

    public function detail()
    {
        return $this->hasOne(Detail::class, 'id', 'id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'film_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'film_categories_link',
            'film_id', 'category_id');
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'film_id');
    }

    public function likes()
    {
        return $this->hasMany(Likes::class, 'film_id')
            ->whereNull('comment_id');
    }

    public function series()
    {
        return $this->belongsTo(Series::class, 'series_id')->withTrashed();
    }

    public function likesComments()
    {
        return $this->hasMany(Likes::class, 'film_id')
            ->whereNotNull('comment_id');
    }

    public function newsletters()
    {
        return $this->hasMany(Newsletter::class, 'film_id');
    }

    public function comments()
    {
        return $this->hasMany(Comments::class);
    }

    public function trashedScreenshots()
    {
        return $this->hasMany(Screenshots::class, 'film_id', 'id')->onlyTrashed();
    }

    public function trashedFiles()
    {
        return $this->hasMany(File::class, 'film_id', 'id')->onlyTrashed();
    }
}
