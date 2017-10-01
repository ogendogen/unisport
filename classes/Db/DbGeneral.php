<?php
namespace {
    require_once("Database.php");
}


namespace Db
{
    class DbGeneral extends Database
    {
        private $db;
        public function __construct($host, $user, $pass, $db)
        {
            try
            {
                $this->db = new parent($host, $user, $pass, $db);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getUserById(int $id) : array
        {
            $ret = array();
            $row = $this->db->exec("SELECT * FROM users WHERE id = ?", $id);
            if (is_null($row) || empty($row)) throw new \Exception("No user found");
            return $row;
        }

        public function isLoginCorrect(string $email, string $pass) : bool
        {
            try
            {
                $salt = $this->db->exec("SELECT user_salt FROM users WHERE user_email = ?", $email);
                if (!$salt) throw new \Exception("No user found");
                $pass = md5(md5($pass).md5($salt[0]));
                if ($this->isRowExists("user_pass", "users", $pass)) return true;
                return false;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function dbIsUserExists(string $login) : bool
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

        public function dbIsUserExistsById(int $id) : bool
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

        public function dbRegisterUser(string $login, string $pass, string $email, string $name, string $surname) : array
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

        public function isConfigurationCorrect(int $id, string $code) : bool
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

        public function resetCode(int $id)
        {
            try
            {
                if ($this->dbIsUserExistsById($id))
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

        public function getUserIdByLogin(string $login, bool $by_email = false) : int
        {
            try
            {
                return intval($this->db->exec("SELECT user_id FROM users WHERE ".($by_email ? "user_email" : "user_login")." = ?", [$login])[0]["user_id"]);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function isActivationCodeEqualsZero(string $login, bool $by_email = false) : bool
        {
            try
            {
                return $this->db->exec("SELECT user_activate_code FROM users WHERE ".($by_email ? "user_email" : "user_login")." = ?", [$login])[0]["user_activate_code"] == "0";
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
    }
}
