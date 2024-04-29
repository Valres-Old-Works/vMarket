<?php

namespace Valres\Market\utils;

class TimeHelper
{
    public static array $timeUnits = [
        "y" => 31536000,
        "M" => 2635200,
        "w" => 604800,
        "d" => 86400,
        "h" => 3600,
        "m" => 60,
        "s" => 1
    ];

    public static function timeToString(int $time): string {
        $timeRestant = $time - time();
        $formatTemp = '';

        foreach(self::$timeUnits as $unit => $value){
            if($timeRestant >= $value){
                $quantity = intval(abs($timeRestant / $value));
                $formatTemp .= $quantity . $unit . ' ';
                $timeRestant -= $quantity * $value;
            }
        }

        return trim($formatTemp);
    }
}
