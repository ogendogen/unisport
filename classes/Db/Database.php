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
                global $host;
                global $user;
                global $pass;
                global $database;
                $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $database, $user, $pass);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch (\PDOException $e)
            {
                throw $e;
                //error("Połączenie z bazą nie powiodło się: " . $e->getMessage());
            }
        }

        protected function exec($query, $params = null) : array
        {
            try
            {
                $retval = null;
                $q = $this->conn->prepare($query);
                if ($q->execute($params)) {
                    if (substr($query, 0, 6) === "SELECT")
                        $retval = $q->fetchAll(PDO::FETCH_ASSOC);
                    $q->closeCursor();
                }
                return $retval;
            }
            catch (\PDOException $e)
            {
                throw $e;
                //error("Błąd bazy: " . $e->getMessage());
            }
        }

        protected function isRowExists(string $column, string $table, string $value) : bool
        {
            try
            {
                $ret = $this->exec("SELECT ".$column." FROM ".$table." WHERE ".$column."= ?", $value);
                if (!$ret) return false;
                return true;
            }
            catch (\PDOException $e)
            {
                throw $e;
            }
        }
    }
}