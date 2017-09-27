<?php

namespace Utils;


class General
{
    public function redirect(string $url) : void
    {
        header("Location: ". $url);
    }

    public static function getIP() : string
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public static function getRandomString(int $len = 16) : string
    {
        return substr(md5(rand()), 0, $len);
    }

    public static function isStartsWithUpper(string $str) : bool
    {
        $chr = mb_substr ($str, 0, 1, "UTF-8");
        return mb_strtolower($chr, "UTF-8") != $chr;
    }

    public static function validatePostArray($array)
    {
        foreach ($array as $key => $value)
        {
            if (empty($value)) throw new \Exception("Uzupełnij wszystkie pola!");
        }
    }
}