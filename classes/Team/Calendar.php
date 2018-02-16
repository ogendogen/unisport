<?php

namespace {
    require_once(__DIR__ . "/../Db/Database.php");
    require_once(__DIR__ . "/../Team/Team.php");
    require_once(__DIR__. "/../Utils/Validations.php");
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
                if (!is_int($teamid)) throw new \Exception("Niepoprawna drużyna!", 21);
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
                $ret = $db->exec("SELECT calendar_teamid FROM calendar WHERE calendar_id = ?", [$event_id]);
                if (empty($ret)) throw new \Exception("Takie wydarzenie nie istnieje!");
                return $ret[0]["calendar_teamid"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public static function getAllTeamsIncomingEvents(int $userid) : array
        {
            try
            {
                $db = \Db\Database::getInstance();
                $user = new \User\LoggedUser($userid);
                $teams = $user->getAllUserTeams();
                $teams_ids = array();
                foreach ($teams as $team) array_push($teams_ids, $team["team_id"]);
                return $db->exec("SELECT calendar_startdate, calendar_event, calendar_priority, calendar_teamid FROM calendar
                                            WHERE UNIX_TIMESTAMP(STR_TO_DATE(calendar.calendar_startdate, '%d.%m.%Y %H:%i')) > UNIX_TIMESTAMP() 
                                            && UNIX_TIMESTAMP(STR_TO_DATE(calendar.calendar_startdate, '%d.%m.%Y %H:%i')) - 259200 < UNIX_TIMESTAMP()
                                            && calendar_teamid IN (?)
                                            ORDER BY calendar_teamid",[implode(',', $teams_ids)]);
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        public function addEvent(string $startdatetime, string $enddatetime, string $event, string $priority) : int
        {
            try
            {
                if (!in_array($priority, $this->priorities)) throw new \Exception("Niepoprawny priorytet!");
                if (!\Utils\Validations::validateDate($startdatetime,"d.m.Y H:s")) throw new \Exception("Format początkowej daty jest niepoprawny!");
                if (!\Utils\Validations::validateDate($enddatetime, "d.m.Y H:s")) throw new \Exception("Format końcowej daty jest niepoprawny!");
                if (strtotime($startdatetime) >= strtotime($enddatetime)) throw new \Exception("Data zakończenia jest wcześniejsza niż rozpoczęcia!");
                if (strlen($event) > 32) throw new \Exception("Opis zdarzenia jest za długi! (maks 32 znaki)");

                //$startdatetime = str_replace("-", ".", $startdatetime);
                //$enddatetime = str_replace("-", ".", $enddatetime);

                $this->db->exec("INSERT INTO calendar SET calendar_teamid = ?,
                                            calendar_startdate = ?,
                                            calendar_enddate = ?,
                                            calendar_event = ?,
                                            calendar_priority = ?", [$this->teamid, $startdatetime, $enddatetime, $event, $priority]);

                return $this->db->getLastInsertId("calendar_id");
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function moveEvent(int $eventid, string $new_startdate, string $new_enddate) : bool
        {
            try
            {
                if (!\Utils\Validations::validateDate($new_startdate,"d.m.Y H:s")) throw new \Exception("Początkowa data jest niepoprawna!");
                if (!\Utils\Validations::validateDate($new_enddate, "d.m.Y H:s")) throw new \Exception("Końcowa data jest niepoprawna!");

                $this->db->exec("UPDATE calendar SET calendar_startdate = ?, calendar_enddate = ? WHERE calendar_id = ? AND calendar_teamid = ?", [$new_startdate, $new_enddate, $eventid, $this->teamid]);
                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getAllTeamEvents() : array
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

        public function deleteEvent(int $eventid) : bool
        {
            try
            {
                $this->db->exec("DELETE FROM calendar WHERE calendar_id = ?", [$eventid]);
                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}