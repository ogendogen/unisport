<?php

namespace {
    //require_once(__DIR__."/../../configs/config.php");
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
                $this->login = $data["user_login"];
                $this->name = $data["user_name"];
                $this->surname = $data["user_surname"];
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
    }
}