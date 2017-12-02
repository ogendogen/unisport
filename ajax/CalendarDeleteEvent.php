<?php

require_once(__DIR__."/../classes/Team/Team.php");
require_once(__DIR__."/../classes/Team/Calendar.php");
require_once(__DIR__."/../classes/Utils/General.php");
session_start();
header('Content-Type: application/json');

try
{
    if (!isset($_SESSION["userid"])) throw new \Exception("Nie jesteś zalogowany!");
    if (!isset($_GET["eventid"]) || !is_numeric($_GET["eventid"])) throw new \Exception("Nieznane wydarzenie");

    $eventid = $_GET["eventid"];
    $teamid = \Team\Calendar::getTeamIdByEventId($eventid);
    if (empty($teamid)) throw new \Exception("Nieznane wydarzenie");

    $team = new \Team\Team($teamid);
    if (!$team->isUserLeader($_SESSION["userid"])) throw new \Exception("Nie jesteś liderem!");

    $calendar = new \Team\Calendar($teamid);
    $calendar->deleteEvent($eventid);

    echo \Utils\General::retJson(1, "success");
}
catch (\Exception $e)
{
    echo \Utils\General::retJson(-1, $e->getMessage());
}

?>