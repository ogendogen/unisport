<?php

namespace {
    require_once(__DIR__."/../Db/Database.php");
    require_once(__DIR__."/Sport.php");
}

namespace Team
{
    class Team
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
            $this->db = \Db\Database::getInstance();
            $this->teamid = $teamid;
            if ($this->teamid > 0)
            {
                if (!$this->isTeamExists()) $this->blocked = true;
                else
                {
                    $teaminfo = $this->getTeamInfo(true);
                    $this->name = $teaminfo["team_name"];
                    $this->desc = $teaminfo["team_description"];
                    $this->leadername = $this->getTeamLeaderName($this->teamid);
                    $this->sport = $teaminfo["team_sport"];
                }
            }
            else
            {
                $this->blocked = true;
            }
        }

        public static function getNameById(int $team_id) : string
        {
            try
            {
                $db = \Db\Database::getInstance();
                return $db->exec("SELECT team_name FROM teams WHERE team_id = ?", [$team_id])[0]["team_name"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function createNewTeam(string $name, string $desc, int $leaderid, string $sport)
        {
            try
            {
                if (empty($name) || empty($desc) || empty($leaderid) || empty($sport)) throw new \Exception("Jedno z pól jest puste!");
                if (strlen($name) > 32) throw new \Exception("Nazwa jest za długa!");
                if (strlen($desc) > 256) throw new \Exception("Opis jest za długi!");
                if (!is_numeric($leaderid)) throw new \Exception("Użytkownik-lider nie istnieje!");
                if (!\Team\Sport::isSportExists($sport)) throw new \Exception("Taki sport nie istnieje w naszej bazie!");
                $this->db->exec("INSERT INTO teams SET
                                  team_name = ?,
                                  team_description = ?,
                                  team_leader = ?,
                                  team_sport = ?,
                                  team_created = ?", [$name, $desc, $leaderid, $sport, time()]);
                $this->blocked = false;
                $this->teamid = $this->db->exec("SELECT team_id FROM teams WHERE team_name = ?", [$name])[0]["team_id"];

                $this->db->exec("INSERT INTO teams_members SET member_teamid = ?, member_userid = ?", [$this->teamid, $leaderid]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function updateTeamInfo(string $name=null, string $desc=null, string $sport=null)
        {
            if ($this->blocked) throw new \Exception("Drużyna nie zdefiniowana (updateTeamInfo)");
            $query = "UPDATE teams SET ";
            $params = array();

            if (is_null($name) && is_null($desc) && is_null($sport)) throw new \Exception("Nic nie zostało zmienione!");
            if (!is_null($name))
            {
                if (strlen($name) > 32) throw new \Exception("Nazwa jest za długa!");
                $query .= "team_name = ?, ";
                array_push($params, $name);
            }
            if (!is_null($desc))
            {
                if (strlen($desc) > 256) throw new \Exception("Opis jest za długi!");
                $query .= "team_description = ?, ";
                array_push($params, $desc);
            }
            if (!is_null($sport))
            {
                if (!\Team\Sport::isSportExists($sport)) throw new \Exception("Taki sport nie istnieje w bazie!");
                $query .= "team_sport = ? ";
                array_push($params, $sport);
            }

            $query .= "WHERE team_id = ?";
            array_push($params, $this->teamid);

            try
            {
                $this->db->exec($query, $params);
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
                $this->db->exec("INSERT INTO teams_members SET member_userid = ?, member_teamid = ?", [$userid, $this->teamid]);
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
            if (!$this->isUserInTeam($userid)) throw new \Exception("Użytkownik nie jest w tej drużynie!");
            try
            {
                $this->deleteFromMembersList($userid, $this->teamid);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function setTeamId(int $teamid)
        {
            try
            {
                if ($this->teamid == $teamid) return;
                $this->teamid = $teamid;
                $teaminfo = $this->getTeamInfo(true);
                $this->name = $teaminfo["team_name"];
                $this->desc = $teaminfo["team_desc"];
                $this->leadername = $teaminfo["team_leadername"];
                $this->sport = $teaminfo["team_sport"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function isTeamExists() : bool
        {
            try
            {
                $ret = $this->db->isRowExists("team_id", "teams", $this->teamid);
                if (!$ret) return false;
                return true;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getTeamInfo(bool $from_db=false) : array
        {
            if ($from_db)
            {
                try
                {
                    $arr = $this->db->exec("SELECT * FROM `teams` WHERE team_id = ?", [$this->teamid])[0];
                    $arr["team_leadername"] = $this->getTeamLeaderName($this->teamid);
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
                $ret = $this->db->exec("SELECT team_leader FROM teams WHERE team_id = ?", [$this->teamid]);//[0]["team_leader"];
                if (empty($ret[0])) return false;
                if (trim($ret[0]["team_leader"]) == trim($userid)) return true;
                return false;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        // DbTeam:
        public function getAllTeamPlayers() : array
        {
            try
            {
                return $this->db->exec("SELECT users.* FROM `users` LEFT JOIN teams_members ON users.user_id = teams_members.member_userid WHERE teams_members.member_teamid = ?", [$this->teamid]);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getTeamLeaderName(int $teamid)
        {
            try
            {
                $ret = $this->db->exec("SELECT users.user_name FROM users LEFT JOIN teams ON users.user_id = teams.team_leader WHERE teams.team_id = ?", [$teamid]);
                if (empty($ret)) throw new \Exception("Drużyna nie istnieje!");
                return $ret[0]["user_name"];
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function deleteFromMembersList(int $userid, int $teamid)
        {
            try
            {
                $this->db->exec("DELETE FROM teams_members WHERE member_userid = ? AND member_teamid = ?", [$userid, $teamid]);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function isTeamHasMember(int $userid, int $teamid) : bool
        {
            try
            {
                $ret = $this->db->exec("SELECT users.user_id FROM users LEFT JOIN teams_members ON users.user_id = teams_members.member_userid WHERE users.user_id = ?", [$userid])[0]["user_id"];
                if (!$ret) return false;
                return true;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getAllUserTeamsAsLeader(int $leaderid) : array
        {
            try
            {
                return $this->db->exec("SELECT teams.*, COUNT(teams_members.member_teamid) AS 'totalmembers' FROM `teams` LEFT JOIN `teams_members` ON teams.team_id = teams_members.member_teamid WHERE teams.team_leader = ?", [$leaderid]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function isUserInTeam(int $userid) : bool
        {
            try
            {
                $ret = $this->db->exec("SELECT member_userid FROM teams_members WHERE member_teamid = ? && member_userid = ?", [$this->teamid, $userid]);
                if (!$ret) return false;
                if (trim($ret[0]["member_userid"]) == $userid) return true;
                return false;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function deleteTeam()
        {
            try
            {
                $this->db->exec("DELETE FROM teams WHERE team_id = ?", [$this->teamid]);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function countMembers() : int
        {
            try
            {
                return intval($this->db->exec("SELECT COUNT(*) AS 'members_sum' FROM teams_members WHERE member_teamid = ?", [$this->teamid])[0]["members_sum"]);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }
    }
}