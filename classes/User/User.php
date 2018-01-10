<?php

namespace {
    require_once(__DIR__."/../Db/Database.php");
}

namespace User
{
    class User
    {
        protected $db;
        public function  __construct()
        {
            $this->db = \Db\Database::getInstance();
        }

        public function getUserById(int $id) : array
        {
            $ret = array();
            $row = $this->db->exec("SELECT * FROM users WHERE id = ?", $id);
            if (is_null($row) || empty($row)) throw new \Exception("Taki użytkownik nie istnieje");
            return $row;
        }

        public function isLoginCorrect(string $email, string $pass) : bool
        {
            try
            {
                $salt = $this->db->exec("SELECT user_salt FROM users WHERE user_email = ?", $email);
                if (!$salt) throw new \Exception("No user found");
                $pass = md5(md5($pass).md5($salt[0]));
                if ($this->db->isRowExists("user_pass", "users", $pass)) return true;
                return false;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function isUserExists(string $login) : bool
        {
            try
            {
                return $this->db->isRowExists("user_login", "users", $login);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function isUserExistsById(int $id) : bool
        {
            try
            {
                return $this->db->isRowExists("user_id", "users", $id);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function registerUser(string $login, string $pass, string $email, string $name, string $surname) : array
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
                        $salt,
                        $email,
                        $activate_code,
                        $registered,
                        0,
                        $ip,
                        $name,
                        $surname
                    ]);

                $ret_array = array();
                $ret_array["id"] = $this->db->exec("SELECT user_id FROM users WHERE user_login = ?", [$login])[0]["user_id"];
                $ret_array["code"] = $activate_code;

                return $ret_array;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function resetCode(int $id)
        {
            try
            {
                if ($this->isUserExistsById($id))
                {
                    $this->db->exec("UPDATE users SET user_activate_code = '0' WHERE user_id = ?", [$id]);
                }
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getHashAndPassword(string $login, bool $by_email = false) : array
        {
            try
            {
                $arr = $this->db->exec("SELECT user_pass, user_salt FROM users WHERE ".($by_email ? "user_email" : "user_login")." = ?", [$login]);
                $ret_arr = array();
                $ret_arr["pass"] = $arr[0]["user_pass"];
                $ret_arr["salt"] = $arr[0]["user_salt"];
                return $ret_arr;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getUserIdByLogin(string $login) : int
        {
            try
            {
                return intval($this->db->exec("SELECT user_id FROM users WHERE ".(\Utils\Validations::isEmail($login) ? "user_email" : "user_login")." = ?", [$login])[0]["user_id"]);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getLoginByEmail(string $email) : string
        {
            try
            {
                return $this->db->exec("SELECT user_login FROM users WHERE user_email = ?", [$email])[0]["user_login"];
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getUserDataById(int $id) : array
        {
            try
            {
                $ret = $this->db->exec("SELECT * FROM users WHERE user_id = ?", [$id]);
                if (empty($ret)) return array();
                return $ret[0];
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getSportByName(string $name) : array
        {
            try
            {
                return $this->db->exec("SELECT * FROM sports WHERE sport_name = ?", [$name])[0];
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getAllSports() : array
        {
            try
            {
                return $this->db->exec("SELECT * FROM sports");
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        // UserGeneral:
        public function validateNewUser(string $login, string $pass, string $pass2, string $email, string $name, string $surname)
        {
            if ($this->isUserExists($login)) throw new \Exception("Taki użytkownik już istnieje!");
            if ($pass != $pass2) throw new \Exception("Hasła różnią się!");
            if (!\Utils\Validations::isEmail($email)) throw new \Exception("Email nie jest poprawny!");
            if (!\Utils\General::isStartsWithUpper($surname[0])) throw new \Exception("Nazwisko powinno zaczynać się od dużej litery!");
            if ($this->db->isRowExists("user_email", "users", $email)) throw new \Exception("Podany adres email jest zajęty!");
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

        public function isActivationCodeEqualsZero(int $id) : bool // zachowane dla kompatybilności wstecznej
        {
            try
            {
                $ret = $this->db->exec("SELECT user_activate_code FROM users WHERE user_id = ?", [$id])[0]["user_activate_code"];
                if (trim($ret) === "0") return true;
                return false;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function checkConfiguration(int $id, string $code) : bool
        {
            try
            {
                $ret = $this->db->exec("SELECT user_activate_code FROM users WHERE user_id = ?", [$id])[0]["user_activate_code"];
                if ($code == $ret) return true;
                return false;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function confirmUser(int $id)
        {
            try
            {
                $this->resetCode($id);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function checkName(string $name) : bool
        {
            $file = fopen(__DIR__."/../../other/names.txt", "r");
            if (!$file) throw new \Exception("Problem z bazą imion");
            while (!feof($file))
            {
                $line = trim(fgets($file));
                if ($line === $name) return true;
            }
            return false;
        }

        public function isPasswordCorrect(string $login, string $plain_pass, bool $by_email)
        {
            try
            {
                $data = $this->getHashAndPassword($login, $by_email);
                if (md5(md5($plain_pass).md5($data["salt"])) != $data["pass"]) throw new \Exception("Hasło jest niepoprawne!");
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function isUserActive(string $login)
        {
            try
            {
                try
                {
                    $by_email = (\Utils\Validations::isEmail($login) ? true : false);
                    $ret = $this->db->exec("SELECT user_activate_code FROM users WHERE ".($by_email ? "user_email" : "user_login")." = ?", [$login])[0]["user_activate_code"];
                    if (trim($ret) != "0") throw new \Exception("Adres email nie został potwierdzony!");
                }
                catch (\PDOException $e)
                {
                    throw $e;
                }
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function emailToLogin(string $email) : string
        {
            try
            {
                return $this->getLoginByEmail($email);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function findUsersByCredentials(string $name, string $surname) : array // zwraca tablicę userów
        {
            $arr = array();
            try
            {
                if (empty($name) && empty($surname)) throw new \Exception("Dane są puste!");
                if (empty($name)) // szukaj po nazwisku
                {
                    $foundusers = $this->db->exec("SELECT user_id FROM users WHERE user_surname = ?", [$surname]);
                    foreach ($foundusers as $userid)
                    {
                        $user = new \User\LoggedUser(intval($userid["user_id"]));
                        array_push($arr, $user);
                    }
                    return $arr;
                }
                else if (empty($surname))
                {
                    $foundusers = $this->db->exec("SELECT user_id FROM users WHERE user_name = ?", [$name]);
                    foreach ($foundusers as $userid)
                    {
                        $user = new \User\LoggedUser(intval($userid["user_id"]));
                        array_push($arr, $user);
                    }
                    return $arr;
                }
                else if (!empty($name) && !empty($surname))
                {
                    $foundusers = $this->db->exec("SELECT user_id FROM users WHERE user_name = ? AND user_surname = ?", [$name, $surname]);
                    foreach ($foundusers as $userid)
                    {
                        $user = new \User\LoggedUser(intval($userid["user_id"]));
                        array_push($arr, $user);
                    }
                    return $arr;
                }
                throw new \Exception("Nieznany błąd");
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}