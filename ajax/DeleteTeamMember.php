<?php

require_once(__DIR__."/../configs/config.php");
require_once(__DIR__."/../classes/Team/Team.php");
require_once(__DIR__."/../classes/Team/Invitation.php");
require_once(__DIR__."/../classes/Utils/General.php");
header('Content-Type: application/json'); // json response

global $CONF;
session_start();

if (!isset($_SESSION["userid"]))
{
    die(\Utils\General::retJson(-1, "Nie jesteś zalogowany!"));
}
else if (!isset($_GET["teamid"]) || !is_numeric($_GET["teamid"]))
{
    die(\Utils\General::retJson(-1, "Nie zdefiniowana drużyna!"));
}
else if (!isset($_GET["kicked"]))
{
    die(\Utils\General::retJson(-1, "Nie zdefiniowani odbiorcy!"));
}

try
{
    $leader = $_SESSION["userid"];
    $teamid = $_GET["teamid"];
    $kicked = $_GET["kicked"];

    $team = new \Team\Team($teamid);
    if (!$team->isUserLeader($leader))
    {
        die(\Utils\General::retJson(0, "Nie jesteś liderem!"));
    }

    $members = null;
    if (strstr($kicked, "|")) $members = explode("|", $kicked);
    else $members = $kicked;

    if (is_array($members))
    {
        foreach ($members as $member)
        {
            if ($team->isUserLeader($member)) die(\Utils\General::retJson(0, "Nie możesz usunąć lidera !"));
            $team->removeMember($member);
        }
    }
    else
    {
        $team->removeMember($kicked);
    }

    die(\Utils\General::retJson(1, "Pomyślnie usunięto zawodników"));
}
catch (\Exception $e)
{
    die(\Utils\General::retJson(-1, $e->getMessage()));
}