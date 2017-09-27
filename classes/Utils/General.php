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
}