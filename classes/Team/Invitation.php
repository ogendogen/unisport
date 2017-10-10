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

        public function __construct(int $userid)
        {
            try
            {
                $this->db = new \Db\DbInvitation();
                $this->userid = $userid;
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

        public function verifyInvitation(int $teamid) : bool
        {
            try
            {
                return $this->db->dbVerifyInvitation($this->userid, $teamid);
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
