<?php

session_start();
if (!isset($_SESSION["userid"])) die(\Utils\General::retJson(-1, "Nie jesteś zalogowany!"));
if (!isset($_GET["teamid"])) die(\Utils\General::retJson(-1, "Nie wybrano drużyny!"));

$userid = $_SESSION["userid"];
$teamid = $_GET["teamid"];

try
{
    require_once(__DIR__."/../classes/Team/Team.php");
    $team = new \Team\Team($teamid);
    if (!$team->isUserLeader($userid)) die(\Utils\General::retJson(0, "Nie jesteś liderem!"));
    $team->deleteTeam();
    die(\Utils\General::retJson(1, "Pomyślnie usunięto drużynę!"));
}
catch (\Exception $e)
{
    \Utils\General::retJson(-1, $e->getMessage());
}

?>