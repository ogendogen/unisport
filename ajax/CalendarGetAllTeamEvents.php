<?php

require_once(__DIR__."/../classes/Team/Team.php");
require_once(__DIR__."/../classes/Team/Calendar.php");
require_once(__DIR__."/../classes/Utils/General.php");
session_start();
header('Content-Type: application/json');

try
{
    if (!isset($_GET["teamid"])) throw new \Exception("Brakujące argumenty");
    if (!isset($_SESSION["userid"])) throw new \Exception("Nie jesteś zalogowany!");
    $teamid = $_GET["teamid"];
    $team = new \Team\Team($teamid);
    if (!$team->isUserInTeam($_SESSION["userid"])) throw new \Exception("Nie jesteś w tej drużynie!");

    $calendar = new \Team\Calendar($teamid);
    $arr = array();
    $arr = $calendar->getAllTeamEvents();

    echo \Utils\General::retJsonArray(1, $arr);
}
catch (\Exception $e)
{
    echo \Utils\General::retJson(-1, $e->getMessage());
}

?>