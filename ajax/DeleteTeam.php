<?php

session_start();
$message = array();

if (!isset($_GET["teamid"]))
{
    $message["code"] = 0;
    $message["msg"] = "Wybierz drużynę";
    die(json_encode($message, JSON_UNESCAPED_UNICODE));
}

if (!isset($_SESSION["userid"]))
{
    $message["code"] = -1;
    $message["msg"] = "Nie jesteś zalogowany!";
    die(json_encode($message, JSON_UNESCAPED_UNICODE));
}

require_once("../classes/Team/Team.php");

$teamid = $_GET["teamid"];
$userid = $_GET["userid"];

try
{
    $team = new \Team\Team($teamid);
    if (!$team->isUserLeader($userid))
    {
        $message["code"] = 0;
        $message["msg"] = "Nie jesteś liderem!";
        die(json_encode($message, JSON_UNESCAPED_UNICODE));
    }
    $team->deleteTeam();
    $message["code"] = 1;
    $message["msg"] = "Pomyślnie usunięto drużynę";
}
catch (\Exception $e)
{
    \Utils\Front::error($e->getMessage());
}

?>