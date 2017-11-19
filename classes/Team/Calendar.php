<?php

namespace {
    require_once(__DIR__ . "/../Db/Database.php");
    require_once(__DIR__ . "/../Team/Team.php");
}

namespace Team
{
    class Calendar
    {
        private $db;
        private $team;
        private $teamid;
        private $priorities = array("low", "medium", "high");
        public function __construct(int $teamid)
        {
            try
            {
                if (!is_int($teamid)) throw new \Exception("Niepoprawna drużyna!");
                $this->team = new \Team\Team($teamid);
                $this->teamid = $teamid;
                $this->db = \Db\Database::getInstance();
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public static function getTeamIdByEventId(int $event_id) : int
        {
            try
            {
                $db = \Db\Database::getInstance();
                return $db->exec("SELECT calendar_teamid FROM calendar WHERE calendar_id = ?", [$event_id])[0];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function addEvent(string $startdate, string $enddate, string $event, string $priority) : int
        {
            try
            {
                if (!in_array($priority, $this->priorities)) throw new \Exception("Niepoprawny priorytet!");
                if (!\Utils\General::validateDate($startdate,"Y-m-d")) throw new \Exception("Początkowa data jest niepoprawna!");
                if (!\Utils\General::validateDate($enddate, "Y-m-d")) throw new \Exception("Końcowa data jest niepoprawna!");
                if (strlen($event) > 32) throw new \Exception("Opis zdarzenia jest za długi! (maks 32 znaki)");

                $this->db->exec("INSERT INTO calendar SET calendar_teamid = ?,
                                            calendar_startdate = ?,
                                            calendar_enddate = ?,
                                            calendar_event = ?,
                                            calendar_priority = ?", [$this->teamid, $startdate, $enddate, $event, $priority]);

                return $this->db->getLastInsertId("calendar_id");
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function moveEvent(int $eventid, string $new_startdate, string $new_enddate)
        {
            try
            {
                if (!\Utils\General::validateDate($new_startdate,"Y-m-d")) throw new \Exception("Początkowa data jest niepoprawna!");
                if (!\Utils\General::validateDate($new_enddate, "Y-m-d")) throw new \Exception("Końcowa data jest niepoprawna!");
                if (!is_int($eventid)) throw new \Exception("Niepoprawne wydarzenie!");

                $this->db->exec("UPDATE calendar SET calendar_startdate = ?, calendar_enddate = ? WHERE calendar_id = ? AND calendar_teamid = ?", [$new_startdate, $new_enddate, $eventid, $this->teamid]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getAllTeamEvents()
        {
            try
            {
                return $this->db->exec("SELECT * FROM calendar WHERE calendar_teamid = ?", [$this->teamid]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}