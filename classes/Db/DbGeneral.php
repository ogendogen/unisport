<?php
namespace {
    require_once("Database.php");
    //require_once("../Utils/General.php");
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
                $this->db = parent::__construct($host, $user, $pass, $db);
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
                $pass = md5(md5($pass).md5($salt));
                if ($this->isRowExists("user_pass", "users", $pass)) return true;
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
                return $this->isRowExists("user_login", "users", $login);
                //$ret = $this->db->exec("SELECT user_login FROM users WHERE user_login = ?", $login);
                //if (!$ret) return false;
                //return true;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
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

                $this->exec("INSERT INTO users SET user_login = ?,
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
    }
}