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
            $db = new \Db\DbGeneral($CONF["db"]["host"], $CONF["db"]["user"], $CONF["db"]["pass"], $CONF["db"]["db"]);
        }

        public function validateNewUser(string $login, string $pass, string $pass2, string $email, string $name, string $surname)
        {
            if (!$this->isUserExists($login)) throw new \Exception("Taki użytkownik już istnieje!");
            //$user = htmlspecialchars(trim($_POST["nick"]));
            //$pass = htmlspecialchars(trim($_POST["pass"]));
            //$pass2 = htmlspecialchars(trim($_POST["pass2"]));
            if ($pass != $pass2) throw new \Exception("Hasła różnią się!");//\Utils\Front::stopWarning("Hasła nie różnią się!");
            //$email = htmlspecialchars(trim($_POST["email"]));
            if (!\Utils\Validations::isEmail($email)) throw new \Exception("Email nie jest poprawny!"); //die(\Utils\Front::modal("Błąd!", "Email nie jest poprawny!", "danger"));
            //$name = htmlspecialchars(trim($_POST["name"]));
            if (!\User\UserGeneral::checkName($name)) throw new \Exception("Takie imię nie istnieje!"); //die(\Utils\Front::modal("Błąd!", "Takie nie istnieje!", "danger"));
            //$surname = htmlspecialchars((trim($_POST["surname"])));
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
                $salt = \Utils\General::getRandomString();
                $salted_pass = md5(md5($pass).md5($salt));
                $activate_code = \Utils\General::getRandomString();
                $ip = \Utils\General::getIP();
                $registered = time();

                $this->db->exec("INSERT INTO users SET user_login = ?,
                                              user_pass = ?,
                                              user_salt = ?,
                                              user_email = ?,
                                              user_activate_code = ?,
                                              user_registered = ?,
                                              user_lastlogin = ?,
                                              user_ip = ?,
                                              user_name = ?,
                                              user_surname = ?",
                    [
                        $login,
                        $salted_pass,
                        $email,
                        $activate_code,
                        $registered,
                        0,
                        $ip,
                        $name,
                        $surname
                    ]);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        private function isUserExists(string $user) : bool
        {
            throw new \Exception("Not yet implemented!");
        }

        private function checkName(string $name) : bool
        {
            $file = fopen(__DIR__."/../../other/names.txt");
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