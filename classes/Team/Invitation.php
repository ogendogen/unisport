<?php

namespace {
    require_once(__DIR__."/../Db/Database.php");
}

namespace Team
{
    class Invitation
    {
        private $db;
        private $userid;
        public function __construct(int $userid = 0)
        {
            try
            {
                $this->db = \Db\Database::getInstance();
                $this->userid = $userid;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function sendInvitation(int $teamid)
        {
            try
            {
                $team = new \Team\Team($teamid);
                if ($team->isUserInTeam($this->userid)) throw new \Exception("Jeden z użytkowników już należy do tej drużyny!");

                $this->db->exec("INSERT INTO invitations SET invitation_invited = ?, invitation_team = ?", [$this->userid, $teamid]);
            }
            catch (\Exception $e)
            {
                if ($e->getCode() == 23000) throw new \Exception("Użytkownik już otrzymał zaproszenie!");
                throw $e;
            }
        }

        public function getAllUserInvitations() : array
        {
            try
            {
                if ($this->userid == 0) throw new \Exception("");
                return $this->db->exec("SELECT invitations.*, teams.team_name, teams.team_id FROM `invitations` LEFT JOIN `teams` ON invitations.invitation_team = teams.team_id WHERE invitations.invitation_invited = ?", [$this->userid]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function verifyInvitation(int $teamid) : bool
        {
            try
            {
                $ret = $this->db->exec("SELECT invitation_id FROM invitations WHERE invitation_invited = ? AND invitation_team = ?", [$this->userid, $teamid]);
                if (!$ret) return false;
                return true;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function acceptInvitation(int $teamid)
        {
            try
            {
                $this->removeInvitation($this->userid, $teamid);
                $this->addUserToTeam($this->userid, $teamid);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function rejectInvitation(int $teamid)
        {
            try
            {
                $this->removeInvitation($this->userid, $teamid);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        // DbInvitation:
        public function dbGetAllUserInvitations(int $userid) : array
        {
            try
            {
                return $this->db->exec("SELECT invitations.*, teams.team_name FROM `invitations` LEFT JOIN `teams` ON invitations.invitation_team = teams.team_id WHERE invitations.invitation_invited = ?", [$userid]);
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

        private function removeInvitation(int $userid, int $teamid)
        {
            try
            {
                $this->db->exec("DELETE FROM invitations WHERE invitation_invited = ? AND invitation_team = ?", [$userid, $teamid]);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        private function addUserToTeam(int $userid, int $teamid)
        {
            try
            {
                $this->db->exec("INSERT INTO teams_members SET member_userid = ?, member_teamid = ?", [$userid, $teamid]);
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
                $ret = $this->db->exec("SELECT invitation_id FROM invitations WHERE invitation_invited = ? AND invitation_team = ?", [$userid, $teamid]);
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
