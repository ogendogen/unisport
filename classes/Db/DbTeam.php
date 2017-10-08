<?php

namespace {
    require_once("Database.php");
    require_once("DbGeneral.php");
    require_once(__DIR__."/../Team/Sport.php");
}

namespace Db
{
    class DbTeam extends Database
    {
        public function __construct()
        {
            parent::__construct();
        }

        public function addTeamRow(string $name, string $desc, int $leaderid, string $sport)
        {
            try
            {
                $this->exec("INSERT INTO teams SET
                                      team_name = ?,
                                      team_desc = ?,
                                      team_leader = ?,
                                      team_sport = ?,
                                      team_created = ?", [$name, $desc, $leaderid, $sport, time()]);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getTeamInfo(int $teamid) : array
        {
            try
            {
                return $this->exec("SELECT * FROM `teams` WHERE team_id = ?", [$teamid])[0];
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getTeamPlayers(int $teamid) : array
        {
            try
            {
                return $this->exec("SELECT * FROM `teams` LEFT JOIN teams_members ON teams.team_id = teams_members.member_teamid WHERE teams.team_id = ?", [$teamid]);
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
                return $this->exec("SELECT users.user_name FROM users LEFT JOIN teams ON users.user_id = teams.team_id WHERE teams.team_id = ?", [$teamid])[0]["user_name"];
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function updateTeam(string $name=null, string $desc=null, string $sport=null)
        {
            $params = array();
            $query = "UPDATE teams SET ";
            if (!is_null($name))
            {
                array_push($params, $name);
                $query .= "team_name = ?";
            }
            if (!is_null($desc))
            {
                array_push($params, $desc);
                $query .= "team_desc = ?";
            }
            if (!is_null($sport))
            {
                array_push($params, $sport);
                $query .= "team_sport = ?";
            }

            try
            {
                $this->exec($query, $params);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function addMemberToTeam(int $userid, int $teamid)
        {
            try
            {
                $this->exec("INSERT INTO teams_members SET member_userid = ?, member_teamid = ?", [$userid, $teamid]);
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
                $this->exec("DELETE FROM teams_members WHERE member_userid = ? AND member_teamid = ?", [$userid, $teamid]);
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
                $ret = $this->exec("SELECT users.user_id FROM users LEFT JOIN teams_members ON users.user_id = teams_members.member_userid WHERE users.user_id = ?", [$userid])[0]["user_id"];
                if (!$ret) return false;
                return true;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function isTeamExists(int $teamid) : bool
        {
            try
            {
                $ret = $this->isRowExists("team_id", "teams", $teamid);
                if (!$ret) return false;
                return true;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function isUserLeader(int $userid, int $teamid) : bool
        {
            try
            {
                $ret = $this->exec("SELECT team_leader FROM teams WHERE team_id = ?", [$teamid])[0]["team_leader"];
                if (trim($ret) == trim($userid)) return true;
                return false;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getAllUserTeams(int $leaderid) : array
        {
            try
            {
                return $this->exec("SELECT teams.*, COUNT(teams_members.member_teamid) AS 'totalmembers' FROM `teams` LEFT JOIN `teams_members` ON teams.team_id = teams_members.member_teamid WHERE teams.team_leader = ?", [$leaderid]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}