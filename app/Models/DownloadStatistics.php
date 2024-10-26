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
        'file_id',
        'is_link',
    ];

    const UPDATED_AT = null;

    public function files()
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }
}
