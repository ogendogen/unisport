<?php

require_once(__DIR__."/../classes/Team/Calendar.php");
require_once(__DIR__."/../classes/Utils/General.php");
session_start();
header('Content-Type: application/json');

try
{
    if (!isset($_GET["eventid"])) throw new \Exception("Nieznane wydarzenie");
    if (!isset($_GET["startdate"]) || !isset($_GET["enddate"])) throw new \Exception("Brakujące parametry");

    $team = new \Team\Team($_GET["teamid"]);
    if (!$team->isUserInTeam($_SESSION["teamid"])) throw new \Exception("Nie jesteś w tej drużynie!");

    $calendar = new \Team\Calendar($_GET["teamid"]);
    $calendar->moveEvent($_GET["eventid"], $_GET["startdate"], $_GET["enddate"]);

    \Utils\General::retJson(1, "success");
}
catch (\Exception $e)
{
    \Utils\General::retJson(-1, $e->getMessage());
}

?>