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
    }
}