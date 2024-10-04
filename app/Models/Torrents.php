<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Torrents extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'torrents';
    protected $fillable = [
        'game_id',
        'repack_id',
        'name',
        'version',
        'size',
        'source',
        'path',
        'is_link',
        'additional_info'
    ];
    protected static array $extendedFile = [
        'torrent',
        'rar'
    ];

    public static function getExtendedFile(): array
    {
        return self::$extendedFile;
    }

    public function detail()
    {
        return $this->hasOne(Detail::class, 'id', 'detail_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id')->withTrashed();
    }

    public function repacks()
    {
        return $this->hasOne(Repacks::class, 'id', 'repack_id');
    }

    public function downloadStatistic()
    {
        return $this->hasMany(DownloadStatistics::class, 'torrent_id', 'id');
    }
}
