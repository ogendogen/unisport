<?php

session_start();
$message = array();

if (!isset($_SESSION["userid"]))
{
    $message["code"] = 0;
    $message["msg"] = "Nie jesteś zalogowany";
    die(json_encode($message, 256));
}

if (!isset($_GET["teamid"]) || !is_numeric($_GET["teamid"]))
{
    $message["code"] = 0;
    $message["msg"] = "Nie wybrałeś drużyny!";
    die(json_encode($message, 256));
}

require_once(__DIR__."/../classes/Team/Team.php");

try
{
    $team = new \Team\Team($_GET["teamid"]);
    if (!$team->isUserLeader($_SESSION["userid"]))
    {
        $message["code"] = 0;
        $message["msg"] = "Nie jesteś liderem drużyny!";
        die(json_encode($message, 256));
    }

    $message["code"] = 1;
    $message["msg"] = "OK";
    die(json_encode($message, 256));
}
catch (\Exception $e)
{
    $message["code"] = -1;
    $message["msg"] = "Błąd: ".$e->getMessage();
    die(json_encode($message, 256));
}

?>