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
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }
    }
}
