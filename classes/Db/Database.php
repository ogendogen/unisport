<?php

namespace Db
{
    use \PDO;
    class Database
    {
        private $conn;
        protected function __construct(string $host, string $user, string $pass, string $database)
        {
            try
            {
                $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $database, $user, $pass);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }

        protected function exec($query, $params = null)
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

        protected function isRowExists(string $column, string $table, string $value) : bool
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
    }
}