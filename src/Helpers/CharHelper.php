<?php


namespace SitPHP\Commands\Helpers;


class CharHelper
{
    protected static $control_char_mapping = [
        '@' => 0,
        'A'=> 1,
        'B'=> 2,
        'C'=> 3,
        'D'=> 4,
        'E'=> 5,
        'F'=> 6,
        'G'=> 7,
        'H'=> 8,
        'I'=> 9,
        'J'=> 10,
        'K'=> 11,
        'L'=> 12,
        'M'=> 13,
        'N'=> 14,
        'O'=> 15,
        'P'=> 16,
        'Q'=> 17,
        'R'=> 18,
        'S'=> 19,
        'T'=> 20,
        'U'=> 21,
        'V'=> 22,
        'W'=> 23,
        'X'=> 24,
        'Y'=> 25,
        'Z'=> 26,
        '['=> 27,
        '\\'=> 28,
        ']'=> 29,
        '^'=> 30,
        '_'=> 31
    ];


    static function isControlKeyChar(string $char, string $key = null){
        if(!isset($key)){
            return ord($char) < 32;
        }
        else {
            $key = strtoupper($key);
            if(!isset(self::$control_char_mapping[$key])){
                throw new \InvalidArgumentException('Invalid control key '.$key);
            }
            return self::$control_char_mapping[$key] === ord($char);
        }
    }
    static function isEscapeChar(string $char){
        return !isset($char[1]) && ord($char[0]) === 27;
    }
    static function isReturnChar(string $char){
        return $char === PHP_EOL;
    }
    static function isBackspaceChar(string $char){
        return $char === "\177";
    }
    static function isSpaceChar(string $char){
        return $char === "\032";
    }
    static function isTabChar(string $char){
        return $char === "\t";
    }
    static function isEscapedChar(string $char){
        return ord($char[0]) === 27;
    }
    static function isArrowChar(string $char){
        if(!self::isEscapedChar($char)){
            return false;
        }
        return ord($char[1]) === 91;
    }
    static function isArrowUpChar(string $char){
        if(!self::isArrowChar($char)){
            return false;
        }
        return ord($char[2]) === 65;
    }
    static function isArrowDownChar(string $char){
        if(!self::isArrowChar($char)){
            return false;
        }
        return ord($char[2]) === 66;
    }
    static function isArrowRightChar(string $char){
        if(!self::isArrowChar($char)){
            return false;
        }
        return ord($char[2]) === 67;
    }
    static function isArrowLeftChar(string $char){
        if(!self::isArrowChar($char)){
            return false;
        }
        return ord($char[2]) === 68;
    }
    static function isContentChar(string $char){
        return !self::isControlKeyChar($char) && !self::isBackspaceChar($char) && !self::isReturnChar($char) && !self::isTabChar($char) && !self::isEscapedChar($char);
    }
}