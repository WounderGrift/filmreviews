<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivateMessages extends Model
{
    use HasFactory;

    protected $table = 'private_messages';

    protected $fillable = [
        'from_id',
        'whom_id',
        'letter',
    ];

    public function fromUser()
    {
        return $this->belongsTo(Users::class, 'from_id', 'id');
    }

    public function whomUser()
    {
        return $this->belongsTo(Users::class, 'whom_id', 'id');
    }
}
