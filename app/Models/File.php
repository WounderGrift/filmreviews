<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'file';
    protected $fillable = [
        'film_id',
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
        'txt',
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

    public function film()
    {
        return $this->belongsTo(Film::class, 'film_id')->withTrashed();
    }

    public function downloadStatistic()
    {
        return $this->hasMany(DownloadStatistics::class, 'file_id', 'id');
    }
}
