<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadStatistics extends Model
{
    use HasFactory;

    protected $table = 'download_statistics';

    protected $fillable = [
        'user_id',
        'torrent_id',
        'is_link',
    ];

    const UPDATED_AT = null;

    public function torrents()
    {
        return $this->hasOne(Torrents::class, 'id', 'torrent_id');
    }
}
