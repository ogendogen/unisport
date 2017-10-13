<?php

namespace {
    require_once(__DIR__."/../Db/Database.php");
}

namespace Team
{
    class Sport
    {
        private $db;
        public function __construct()
        {
            try
            {
                $this->db = \Db\Database::getInstance();
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getAllSports() : array
        {
            try
            {
                return $this->db->exec("SELECT * FROM sports");
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public static function isSportExists(string $name) : bool
        {
            try
            {
                $static_db = \Db\Database::getInstance();
                $sport = $static_db->exec("SELECT * FROM sports WHERE sport_name = ?", [$name])[0];
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