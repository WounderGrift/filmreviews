<?php

namespace App\Http\Helpers;

class TextHelper
{
    public static function fiveSentences(string $text, int $length): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $firstFiveSentences = array_slice($sentences, 0, $length);
        return implode(' ', $firstFiveSentences);
    }
}
