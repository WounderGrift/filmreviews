<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverdueTgReport extends Model
{
    use HasFactory;

    protected $table = 'overdue_tg_report';

    protected $fillable = [
        'date_send'
    ];

    public $timestamps = false;
}
