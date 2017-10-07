<?php

namespace {
    require_once(__DIR__."/../Db/DbGeneral.php");
}

namespace Team
{
    class Sport
    {
        public static function isSportExists(string $name) : bool
        {
            try
            {
                $db = new \Db\DbGeneral();
                $sport = $db->getSportByName($name);
                if (trim($name) == trim($sport["sport_name"])) return true;
                return false;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}