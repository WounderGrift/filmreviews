<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Harvester extends Model
{
    use HasFactory;

    protected $table = 'harvester_log';
    protected $fillable = [
        'game_id',
        'name',
        'url',
        'page_count',
        'source',
        'action',
        'status',
    ];
    protected static array $status = [
        'jump',
        'collecting',
        'filling',
        'created',
        'updated',
        'broken'
    ];
    protected static array $sources = [
      'mass',
      'xatab'
    ];
    protected static array $actions = [
        'play',
        'rewrite',
        'check'
    ];
    protected static array $nameRow = [
        'Init:main_page',
        'Jump:item:url',
        'Lick:item:count'
    ];

    public static function getStatus(): array
    {
        return self::$status;
    }

    public static function getSources(): array
    {
        return self::$sources;
    }

    public static function getActions(): array
    {
        return self::$actions;
    }

    public static function getNameRow(): array
    {
        return self::$nameRow;
    }

    public function setStatus($newStatus): void
    {
        if (in_array($newStatus, self::$status)) {
            $this->status = $newStatus;
            $this->save();
        }
    }

    public function checkShowDoubleStatus(): bool
    {
        $forShowSecondStatus = [
            self::$status[3],
            self::$status[4]
        ];

        if (in_array($this->status, $forShowSecondStatus))
            return true;
        return false;
    }

    public function game()
    {
        return $this->hasOne(Game::class, 'id', 'game_id');
    }
}
