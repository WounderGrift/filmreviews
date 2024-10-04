<?php

namespace App\Http\Helpers;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Exception;

class DateHelper
{
    public static function dateFormatterJFY($date, $timezone): string
    {
        $carbonDate     = Carbon::parse($date)->setTimezone($timezone);
        $formatted_date = $carbonDate->format('j F Y года');

        $months_eng = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $months_rus = [
            'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня',
            'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'
        ];

        return str_replace($months_eng, $months_rus, $formatted_date);
    }

    public static function getLastActivity($timestamp, $timezone): string
    {
        $lastOnline = Carbon::parse($timestamp)->setTimezone($timezone);
        $now        = Carbon::now();

        if ($lastOnline->diffInMinutes($now) < 10)
        {
            $label = 'Сейчас онлайн';
        } elseif ($lastOnline->diffInMinutes($now) < 60) {
            $label = 'Только что';
        } elseif ($lastOnline->isToday()) {
            $label = $lastOnline->diffForHumans();
        } elseif ($lastOnline->isYesterday()) {
            $label = 'Вчера в ' . $lastOnline->format('H:i');
        } else {
            $label = $lastOnline->format('d.m.Y в H:i');
        }

        return $label;
    }

    public static function dateFormatterForDetail($date, $timezone): string
    {
        try {
            if (strlen($date) < 5)
                return $date;

            $carbonDate = Carbon::parse($date)->setTimezone($timezone);
            $formatted_date = $carbonDate->format('j F Y');

            $months_eng = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            $months_rus = [
                'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня',
                'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'
            ];

            return str_replace($months_eng, $months_rus, $formatted_date);
        } catch (Exception $e) {
            return $date;
        }
    }

    public static function dateFormatterForDateReleaseView($dateString): string
    {
        try {
            if (strlen($dateString) < 5)
                return empty($dateString) ? (new DateTime())->format('d F Y') : $dateString;

            $dateTime = new DateTime($dateString);
            return $dateTime->format('d F Y');
        } catch (Exception $e) {
            return $dateString;
        }
    }

    public static function checkIsWaiting($dateString): bool
    {
        try {
            $dateRelease = Carbon::parse($dateString)->startOfDay();
            $dateToday   = Carbon::now()->startOfDay();
            return $dateRelease->greaterThan($dateToday);
        } catch (Exception $e) {
            return false;
        }
    }

    public static function dateFormatterForComments($date, $timezone): string
    {
        $carbonDate = Carbon::parse($date)->setTimezone($timezone);
        return $carbonDate->format('d.m.y H:i');
    }

    public static function isValidTimeZone(string $timezone): bool
    {
        $validTimeZones = DateTimeZone::listIdentifiers();
        return in_array($timezone, $validTimeZones);
    }
}
