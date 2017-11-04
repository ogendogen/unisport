<?php

namespace {
    require_once(__DIR__."/../Db/Database.php");
}

namespace Utils
{
    class Dictionary
    {
        public static function keyToWord(string $key) : string
        {
            try
            {
                $db = \Db\Database::getInstance();
                $ret = $db->exec("SELECT dictionary_translation FROM dictionary WHERE dictionary_key = ?", [$key]);
                if (!$ret) return $key;
                return $ret[0]["dictionary_translation"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public static function wordToKey(string $word) : string
        {
            try
            {
                $db = \Db\Database::getInstance();
                $ret = $db->exec("SELECT dictionary_key FROM dictionary WHERE dictionary_translation = ?", [$word]);
                if (!$ret) return $word;
                return $ret[0]["dictionary_key"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}