<?php

namespace {
    require_once(__DIR__."/../Db/DbTeam.php");
}

namespace Team
{
    class TeamGeneral
    {
        private $db;
        private $name;
        private $desc;
        private $leadername;
        private $sport;
        private $teamid;
        private $blocked = false;
        public function __construct(int $teamid=0)
        {
            $this->db = new \Db\DbTeam();
            $this->teamid = $teamid;
            if ($this->teamid > 0)
            {
                $teaminfo = $this->db->getTeamInfo($this->teamid);
                $this->name = $teaminfo["team_name"];
                $this->desc = $teaminfo["team_desc"];
                $this->leadername = $this->db->getTeamLeaderName($this->teamid);
                $this->sport = $teaminfo["team_sport"];
            }
            else
            {
                $this->blocked = true;
            }
        }

        public function createNewTeam(string $name, string $desc, int $leaderid, string $sport)
        {
            try
            {
                if (empty($name) || empty($desc) || empty($leaderid) || empty($sport)) throw new \Exception("Jedno z pól jest puste!");
                if (strlen($name) > 32) throw new \Exception("Nazwa za długa!");
                if (strlen($desc) > 256) throw new \Exception("Opis za długi!");
                if (!is_numeric($leaderid)) throw new \Exception("Użytkownik-lider nie istnieje!");
                if (!\Team\Sport::isSportExists($name)) throw new \Exception("Taki sport nie istnieje w naszej bazie!");
                $this->db->addTeamRow($name, $desc, $leaderid, $sport);
                $this->blocked = false;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function updateTeamInfo(string $name=null, string $desc=null, string $sport=null)
        {
            if ($this->blocked) throw new \Exception("Drużyna nie zdefiniowana (updateTeamInfo)");
            if (is_null($name) && is_null($desc) && is_null($sport)) return;
            $query = "UPDATE teams SET ";
            if (!is_null($name))
            {
                if (strlen($name) > 32) throw new \Exception("Nazwa jest za długa!");
                $query .= "team_name = ?";
            }
            if (!is_null($desc))
            {
                if (strlen($desc) > 256) throw new \Exception("Opis jest za długi!");
                $query .= "team_desc = ?";
            }
            if (!is_null($sport))
            {
                if (!\Team\Sport::isSportExists($sport)) throw new \Exception("Taki sport nie istnieje w bazie!");
                $query .= "team_sport = ?";
            }

            try
            {
                $this->db->updateTeam($name, $desc, $sport);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function addNewMember(int $userid)
        {
            if ($this->blocked) throw new \Exception("Drużyna nie zdefiniowana (addNewMember)");
            if (!is_numeric($userid)) throw new \Exception("Niepoprawny użytkownik!");
            try
            {
                $this->db->addMemberToTeam($userid, $this->teamid);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function removeMember(int $userid)
        {
            if ($this->blocked) throw new \Exception("Drużyna nie zdefiniowana (removeMember)");
            if (!is_numeric($userid)) throw new \Exception("Niepoprawny użytkownik!");
            try
            {
                $this->db->deleteFromMembersList($userid, $this->teamid);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function isTeamExists() : bool
        {
            if ($this->teamid == 0) return false;
            return true;
        }

        public function getTeamInfo(bool $from_db=false) : array
        {
            if ($from_db)
            {
                try
                {
                    $arr = $this->db->getTeamInfo($this->teamid);
                    $arr["team_leadername"] = $this->db->getTeamLeaderName($this->teamid);
                    return $arr;
                }
                catch (\Exception $e)
                {
                    throw $e;
                }
            }
            else
            {
                $ret = array();
                $ret["team_name"] = $this->name;
                $ret["team_id"] = $this->teamid;
                $ret["team_desc"] = $this->desc;
                $ret["team_leadername"] = $this->leadername;
                $ret["team_sport"] = $this->sport;
                $ret["team_teamid"] = $this->teamid;
                return $ret;
            }
        }

        public function isUserLeader(int $userid) : bool
        {
            try
            {
                return $this->db->isUserLeader($userid, $this->teamid);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getAllUserTeams(int $userid) : array
        {
            try
            {
                return $this->db->getAllUserTeams($userid);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}