<?php

namespace {
    require_once(__DIR__."/../../configs/config.php");
}

namespace Db
{
    use \PDO;
    class Database
    {
        private $conn;
        private static $instance;
        private function __construct()
        {
            try
            {
                global $CONF;
                $host = $CONF["db"]["host"];
                $user = $CONF["db"]["user"];
                $pass = $CONF["db"]["pass"];
                $database = $CONF["db"]["db"];
                $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $database, $user, $pass);
                $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("SET names utf8");
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public static function getInstance()
        {
            if (self::$instance == null) self::$instance = new Database();
            return self::$instance;
        }

        public function exec($query, $params = null)
        {
            try
            {
                $retval = null;
                $q = $this->conn->prepare($query);
                if ($q->execute($params)) {
                    if (substr($query,0,6) === "SELECT")
                        $retval = $q->fetchAll(PDO::FETCH_ASSOC);
                    $q->closeCursor();
                }
                return $retval;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function isRowExists(string $column, string $table, string $value) : bool
        {
            try
            {
                $ret = $this->exec("SELECT ".$column." FROM ".$table." WHERE ".$column."= ?", [$value]);
                if (is_null($ret) || empty($ret) || !$ret) return false;
                return true;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        public function getEnumPossibleValues($table, $field) : array
        {
            try
            {
                $type = $this->conn->query("SHOW COLUMNS FROM `".$table."` LIKE '".$field."'");
                $type->execute();
                $ret = $type->fetchAll(PDO::FETCH_ASSOC);
                $enum_ret = $ret[0]["Type"];
                preg_match("/^enum\(\'(.*)\'\)$/", $enum_ret, $matches);
                $enum = explode("','", $matches[1]);
                return $enum;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }
    }
}