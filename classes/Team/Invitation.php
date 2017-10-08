<?php

namespace {
    require_once(__DIR__."/../Db/DbTeam.php");
}

namespace Team
{
    class Invitation
    {
        private $db;
        public function __construct()
        {
            $this->db = new \Db\DbInvitation();
        }

        public function getAllUserInvitations(int $userid) : array
        {
            try
            {
                return $this->db->dbGetAllUserInvitations($userid);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}
