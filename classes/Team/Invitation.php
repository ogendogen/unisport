<?php

namespace {
    require_once(__DIR__."/../Db/DbInvitation.php");
}

namespace Team
{
    class Invitation
    {
        private $db;
        private $userid;
        private $teamid;

        public function __construct(int $userid, int $teamid)
        {
            try
            {
                $this->db = new \Db\DbInvitation();
                $this->userid = $userid;
                $this->teamid = $teamid;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getAllUserInvitations() : array
        {
            try
            {
                return $this->db->dbGetAllUserInvitations($this->userid);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function verifyInvitation() : bool
        {
            try
            {
                return $this->db->dbVerifyInvitation($this->userid, $this->teamid);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function acceptInvitation()
        {
            try
            {
                $this->db->removeInvitation($this->userid, $this->teamid);
                $this->db->addUserToTeam($this->userid, $this->teamid);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}
