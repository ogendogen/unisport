<?php

namespace {
    require_once(__DIR__."/../Db/Database.php");
}

namespace Team
{
    class Sport
    {
        private $db;
        private $sport_id;
        private $sport_name;
        public function __construct(int $sport_id = 0)
        {
            try
            {
                $this->db = \Db\Database::getInstance();
                if ($sport_id > 0)
                {
                    $ret = $this->db->exec("SELECT * FROM sports WHERE sport_id = ?", [$sport_id]);
                    $this->sport_id = $sport_id;
                    $this->sport_name = $ret[0]["sport_name"];
                }
            }
            catch (\PDOException $e)
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

        public function getAllSportActions() : array
        {
            try
            {
                if ($this->sport_id == 1)
                {
                    return $this->db->getEnumPossibleValues("games_players_football_info", "football_action");
                }
                return $this->db->getEnumPossibleValues("games_players_general_info", "general_action");
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public static function sportIdToName(int $id) : string
        {
            try
            {
                $static_db = \Db\Database::getInstance();
                $sportname = $static_db->exec("SELECT sport_name FROM sports WHERE sport_id = ?",[$id])[0]["sport_name"];
                return $sportname;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public static function isSportExists(string $name) : bool
        {
            try
            {
                $static_db = \Db\Database::getInstance();
                $sport = $static_db->exec("SELECT * FROM sports WHERE ".(is_numeric($name) ? "sport_id" : "sport_name")." = ?", [$name]);
                if (!$sport) return false;
                if (trim($name) == trim($sport[0][(is_numeric($name) ? "sport_id" : "sport_name")])) return true;
                return false;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}