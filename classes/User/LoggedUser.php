<?php

namespace {
    require_once("User.php");
}

namespace User
{
    class LoggedUser extends User
    {
        private $id;
        private $login;
        private $name;
        private $surname;

        public function __construct(int $id)
        {
            try
            {
                parent::__construct();
                $this->id = $id;
                $this->getUserData();
                if (!$this->isUserExistsById($id)) throw new \Exception("Taki użytkownik nie istnieje!");
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function getUserData()
        {
            try
            {
                $data = $this->getUserDataById($this->id);
                if (empty($data)) throw new \Exception("Nie odnaleziono użytkownika o id ".$this->id);

                $this->login = $data["user_login"];
                $this->name = $data["user_name"];
                $this->surname = $data["user_surname"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getUserNotepad() : string
        {
            try
            {
                return $this->db->exec("SELECT user_notepad FROM users WHERE user_id = ?", [$this->id])[0]["user_notepad"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function setUserNotepad(string $notepad)
        {
            try
            {
                $this->db->exec("UPDATE users SET user_notepad = ? WHERE user_id = ?", [$notepad, $this->id]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function isUserActivated() : bool
        {
            try
            {
                $ret = $this->db->exec("SELECT user_activate_code FROM users WHERE user_id = ?", [$this->id])[0]["user_activate_code"];
                if (trim($ret) == "0") return true;
                return false;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getUserCredentials() : string
        {
            try
            {
                $data = $this->getUserDataById($this->id);
                return $data["user_name"]." ".$data["user_surname"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getUserId() : int
        {
            return $this->id;
        }

        public function getAllUserTeams() : array
        {
            try
            {
                return $this->db->exec("SELECT teams.* FROM `teams` LEFT JOIN `teams_members` ON teams.team_id = teams_members.member_teamid WHERE teams_members.member_userid = ?", [$this->id]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}