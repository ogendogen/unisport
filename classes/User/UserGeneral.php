<?php

namespace
{
    require_once(__DIR__."/../Db/DbGeneral.php");
}

namespace User
{
    class UserGeneral
    {
        private $db;
        private $id;
        private $login;
        private $email;

        public function __construct()
        {
            global $CONF;
            $db = new \Db\DbGeneral($CONF["db"]["host"], $CONF["db"]["user"], $CONF["db"]["pass"], $CONF["db"]["db"]);
        }

        public static function checkName(string $name) : bool
        {
            $file = fopen(__DIR__."/../../other/names.txt");
            if (!$file) throw new \Exception("No names.txt file or cannot open!");
            while (!feof($file))
            {
                $line = fgets($file);
                if ($line == $name) return true;
            }
            return false;
        }
    }
}