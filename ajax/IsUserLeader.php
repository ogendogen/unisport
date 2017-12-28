<?php

require_once(__DIR__."/../configs/config.php");
require_once(__DIR__."/../classes/Team/Team.php");
require_once(__DIR__."/../classes/Utils/General.php");
header('Content-Type: application/json'); // json response

session_start();

if (!isset($_SESSION["userid"]))
{
    die(\Utils\General::retJson(-1, "Nie jesteś zalogowany!"));
}
else if (!isset($_GET["teamid"]) || !is_numeric($_GET["teamid"]))
{
    die(\Utils\General::retJson(-1, "Nie zdefiniowana drużyna!"));
}

try
{
    $team = new \Team\Team($_GET["teamid"]);
    if ($team->isUserLeader($_SESSION["userid"]))
    {
        die(\Utils\General::retJson(1, "ok"));
    }
    else
    {
        die(\Utils\General::retJson(0, "not"));
    }
}
catch (\Exception $e)
{
    die(\Utils\General::retJson(-1, $e->getMessage()));
}
?>