<?php

namespace {
    require_once(__DIR__."/../Db/DbGeneral.php");
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
                $this->db = new \Db\DbGeneral();
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
                return $this->db->dbGetAllSports();
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        public function isSportExists(string $name) : bool
        {
            try
            {
                $sport = $this->db->getSportByName($name);
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