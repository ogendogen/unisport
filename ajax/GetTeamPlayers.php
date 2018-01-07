<?php

require_once(__DIR__."/../classes/Utils/General.php");
require_once(__DIR__."/../classes/User/User.php");
require_once(__DIR__."/../classes/Team/Team.php");
session_start();
header('Content-Type: application/json');

try
{
    if (!isset($_GET["teamid"])) throw new \Exception("Taka drużyna nie istnieje!");
    if (!isset($_SESSION["userid"])) throw new \Exception("Nie jesteś zalogowany!");
    $teamid = $_GET["teamid"];

    $team = new \Team\Team($teamid);
    if (!$team->isTeamExists()) throw new \Exception("Taka drużyna nie istnieje!");

    $players = $team->getAllTeamPlayers();
    $ret = array();
    foreach ($players as $player)
    {
        $row["user_id"] = $player["user_id"];
        $row["user_name"] = $player["user_name"];
        $row["user_surname"] = $player["user_surname"];
        array_push($ret, $row);
    }

    echo \Utils\General::retJsonArray(1, $ret);
}
catch (\Exception $e)
{
    echo \Utils\General::retJson(-1, $e->getMessage());
}


?>