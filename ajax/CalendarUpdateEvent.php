<?php

require_once(__DIR__."/../classes/Team/Team.php");
require_once(__DIR__."/../classes/Team/Calendar.php");
require_once(__DIR__."/../classes/Utils/General.php");
session_start();
header('Content-Type: application/json');

try
{
    if (!isset($_SESSION["userid"])) throw new \Exception("Nie jesteś zalogowany!");
    if (!isset($_GET["eventid"])) throw new \Exception("Nieznane wydarzenie");
    if (!isset($_GET["startdate"]) || !isset($_GET["enddate"])) throw new \Exception("Brakujące parametry");

    $teamid = $_GET["teamid"];
    $eventid = $_GET["eventid"];
    $startdate = urldecode($_GET["startdate"]);
    $enddate = urldecode($_GET["enddate"]);

    if (!isset($teamid)) throw new \Exception("Nie wybrano drużyny!");
    $team = new \Team\Team($teamid);
    if (!$team->isUserLeader($_SESSION["userid"])) throw new \Exception("Nie jesteś liderem!");

    $calendar = new \Team\Calendar($teamid);
    $calendar->moveEvent($eventid, $startdate, $enddate);

    echo \Utils\General::retJson(1, "success");
}
catch (\Exception $e)
{
    echo \Utils\General::retJson(-1, $e->getMessage());
}

?>