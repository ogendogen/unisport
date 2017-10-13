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
                return !$this->isActivationCodeEqualsZero($this->id);
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
    }
}