<?php

require_once(__DIR__."/../classes/Team/Calendar.php");
require_once(__DIR__."/../classes/Utils/General.php");
session_start();
header('Content-Type: application/json');

try
{
    if (!isset($_GET["teamid"])) throw new \Exception("Brakujące argumenty");

    $team = new \Team\Team($_GET["teamid"]);
    if (!$team->isUserInTeam($_SESSION["teamid"])) throw new \Exception("Nie jesteś w tej drużynie!");

    $calendar = new \Team\Calendar($_GET["teamid"]);
    $arr = array();
    $arr = $calendar->getAllTeamEvents();

    \Utils\General::retJsonArray(1, $arr);
}
catch (\Exception $e)
{
    \Utils\General::retJson(-1, $e->getMessage());
}

?>