<?php

namespace
{
    require_once(__DIR__."/../Db/DbGeneral.php");
}

namespace User
{
    class UserGeneral
    {
        private $db;
        private $id;
        private $login;
        private $email;

        public function __construct()
        {
            global $CONF;
            $this->db = new \Db\DbGeneral($CONF["db"]["host"], $CONF["db"]["user"], $CONF["db"]["pass"], $CONF["db"]["db"]);
        }

        public function validateNewUser(string $login, string $pass, string $pass2, string $email, string $name, string $surname)
        {
            if ($this->isUserExists($login)) throw new \Exception("Taki użytkownik już istnieje!");
            if ($pass != $pass2) throw new \Exception("Hasła różnią się!");
            if (!\Utils\Validations::isEmail($email)) throw new \Exception("Email nie jest poprawny!");
            //if (!\User\UserGeneral::checkName($name)) throw new \Exception("Takie imię nie istnieje!"); // todo
            if (!\Utils\General::isStartsWithUpper($surname[0])) throw new \Exception("Nazwisko powinno zaczynać się od dużej litery!");
        }

        public function normalizeSurname(string $surname) : string
        {
            $surname[0] = strtoupper($surname[0]);
            for ($i = 1; $i < strlen($surname); $i++)
            {
                if ($surname[$i] == "-" || $surname[$i] == " ") continue;
                $surname[$i] = strtolower($surname[$i]);
            }
            return $surname;
        }

        public function registerUser(string $login, string $pass, string $email, string $name, string $surname)
        {
            try
            {
                $this->db->dbRegisterUser($login, $pass, $email, $name, $surname);
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        private function isUserExists(string $user) : bool
        {
            try
            {
                return $this->db->dbIsUserExists($user);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        private function checkName(string $name) : bool
        {
            throw new \Exception("Method checkName not yet implemented!");
            $file = fopen(__DIR__."/../../other/names.txt", "r");
            if (!$file) throw new \Exception("No names.txt file or cannot open!");
            while (!feof($file))
            {
                $line = fgets($file);
                if ($line == $name) return true;
            }
            return false;
        }
    }
}