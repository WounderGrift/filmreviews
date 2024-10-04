<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repacks extends Model
{
    use HasFactory;

    protected $table = 'repacks';

    protected $fillable = [
        'label',
        'url',
    ];

    public $timestamps = false;

    public function games()
    {
        return $this->belongsToMany(Game::class, Torrents::class, 'repack_id', 'game_id');
    }

    public function torrent()
    {
        return $this->belongsTo(Torrents::class);
    }
}
