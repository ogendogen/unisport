<?php

require_once(__DIR__."/../classes/Team/Team.php");
require_once(__DIR__."/../classes/Team/Calendar.php");
require_once(__DIR__."/../classes/Utils/General.php");
session_start();
header('Content-Type: application/json');

try
{
    \Utils\General::retJson(-1, "aaa");
    die;
    if (!isset($_SESSION["userid"])) throw new \Exception("Nie jesteś zalogowany!");
    if (!isset($_GET["eventid"])) throw new \Exception("Nieznane wydarzenie"); // z tym if'em się wywala
    //if (!isset($_GET["startdate"]) || !isset($_GET["enddate"])) throw new \Exception("Brakujące parametry");

    $team = new \Team\Team($_GET["teamid"]);
    if (!$team->isUserLeader($_SESSION["userid"])) throw new \Exception("Nie jesteś liderem!");

    $calendar = new \Team\Calendar($_GET["teamid"]);
    $calendar->moveEvent($_GET["eventid"], $_GET["startdate"], $_GET["enddate"]);

    \Utils\General::retJson(1, "success");
}
catch (\Exception $e)
{
    \Utils\General::retJson(-1, $e->getMessage());
}

?>