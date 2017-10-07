<?php

namespace {
    require_once(__DIR__."/../../configs/config.php");
    require_once("UserGeneral.php");
}

namespace User
{
    class LoggedUser extends UserGeneral
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
                $data = $this->db->getUserDataById($this->id);
                $this->login = $data["user_login"];
                $this->name = $data["user_name"];
                $this->surname = $data["user_surname"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function isUserActivated()
        {
            try
            {
                return $this->db->isActivationCodeEqualsZero($this->login);
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
                $data = $this->db->getUserDataById($this->id);
                return $data["user_name"]." ".$data["user_surname"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}