<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearReleases extends Model
{
    use HasFactory;

    protected $table = 'years_releases';

    protected $fillable = [
        'year'
    ];

    public $timestamps = false;
}
