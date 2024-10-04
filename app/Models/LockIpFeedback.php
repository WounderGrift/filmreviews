<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LockIpFeedback extends Model
{
    use HasFactory;

    protected $table = 'lock_ip_feedback';

    protected $fillable = [
        'ip',
        'count'
    ];
}
