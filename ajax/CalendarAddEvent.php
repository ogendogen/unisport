<?php

require_once(__DIR__."/../classes/Team/Calendar.php");
require_once(__DIR__."/../classes/Utils/General.php");
session_start();
header('Content-Type: application/json');

try
{
    if (!isset($_GET["teamid"]) || !isset($_GET["startdate"]) || !isset($_GET["enddate"]) || !isset($_GET["event"]) || !isset($_GET["priority"])) throw new \Exception("Brakujące argumenty");
    if (!isset($_SESSION["userid"])) throw new \Exception("Nie jesteś zalogowany!");
    $teamid = $_GET["teamid"];
    $startdate = $_GET["startdate"];
    $enddate = $_GET["enddate"];
    $event = $_GET["event"];
    $priority = $_GET["priority"];

    $team = new \Team\Team($teamid);
    if (!$team->isUserLeader($_SESSION["userid"])) throw new \Exception("Nie jesteś liderem!");

    $calendar = new \Team\Calendar($teamid);
    $calendar->addEvent($startdate, $enddate, $event, $priority);

    \Utils\General::retJson(1, "success");
}
catch (\Exception $e)
{
    \Utils\General::retJson(-1, $e->getMessage());
}


?>