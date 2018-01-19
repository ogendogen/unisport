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
        private $sport_owner;
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
                    $this->sport_owner = $ret[0]["sport_owner"];
                }
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public static function createNewSport(string $name, int $owner) : Sport
        {
            try
            {
                $db = \Db\Database::getInstance();
                $db->exec("INSERT INTO `sports` SET sport_name = ?, sport_owner = ?", [$name, $owner]);
                $sport_id = $db->getLastInsertId("sport_id");
                return new Sport($sport_id);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public static function getAllUsersSports(int $user_id) : array
        {
            try
            {
                $db = \Db\Database::getInstance();
                $ret = $db->exec("SELECT * FROM `sports` WHERE sport_owner = ?", [$user_id]);
                if (empty($ret)) return array();
                return $ret;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function isUserOwner(int $user_id) : bool
        {
            try
            {
                $ret = $this->db->exec("SELECT sport_owner FROM `sports` WHERE sport_owner = ?", [$user_id]);
                if (empty($ret)) return false;
                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function isSportCustom() : bool
        {
            return ($this->sport_owner != "-1" ? true : false);
        }

        public function getSportName()
        {
            return $this->sport_name;
        }

        public function deleteSport()
        {
            try
            {
                $this->db->exec("DELETE FROM `sports` WHERE sport_id = ?", [$this->sport_id]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function addOrChangeActions(array $actions)
        {
            try
            {
                $this->db->exec("DELETE FROM `sport_dictionary` WHERE sport_dictionary_sportid = ?", [$this->sport_id]);
                foreach ($actions as $action) $this->db->exec("INSERT INTO `sport_dictionary` SET sport_dictionary_sportid = ?, sport_dictionary_key = ?", [$this->sport_id, $action]);
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

        public function getAllSportsForUser(int $userid) : array
        {
            try
            {
                return $this->db->exec("SELECT * FROM `sports` WHERE sport_owner = -1 OR sport_owner = ?", [$userid]);
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        public function getAllSportActions() : array
        {
            try
            {
                $raw = $this->db->exec("SELECT sport_dictionary_key FROM `sport_dictionary` WHERE sport_dictionary_sportid = ?", [$this->sport_id]);
                $ret = array();
                foreach ($raw as $row) array_push($ret, $row["sport_dictionary_key"]);
                return $ret;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function changeName(string $new_name)
        {
            try
            {
                $this->db->exec("UPDATE `sports` SET sport_name = ? WHERE sport_id = ?", [$new_name, $this->sport_id]);
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