<?php

namespace {
    require_once(__DIR__."/Database.php");
}

namespace Db
{
    class DbInvitation extends Database
    {
        public function __construct()
        {
            parent::__construct();
        }

        public function dbGetAllUserInvitations(int $userid) : array
        {
            try
            {
                return $this->exec("SELECT invitations.*, teams.team_name FROM `invitations` LEFT JOIN `teams` ON invitations.invitation_team = teams.team_id WHERE invitations.invitation_invited = ?", [$userid]);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function dbVerifyInvitation(int $userid, int $teamid) : bool
        {
            try
            {
                return $this->isUserInvited($userid, $teamid);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function removeInvitation(int $userid, int $teamid)
        {
            try
            {
                $this->exec("DELETE FROM invitations WHERE invitation_invited = ? AND invitation_team = ?", [$userid, $teamid]);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function addUserToTeam(int $userid, int $teamid)
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

        private function isUserInvited(int $userid, int $teamid) : bool
        {
            try
            {
                $ret = $this->exec("SELECT invitation_id FROM invitations WHERE invitation_invited = ? AND invitation_team = ?", [$userid, $teamid]);
                if (!$ret) return false;
                return true;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }
    }
}