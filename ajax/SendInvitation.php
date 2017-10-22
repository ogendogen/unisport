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
else if (!isset($_GET["receivers"]))
{
    die(\Utils\General::retJson(-1, "Nie zdefiniowani odbiorcy!"));
}

try
{
    $teamid = $_GET["teamid"];
    $team = new \Team\Team($teamid);
    if (!$team->isTeamExists())
    {
        die(\Utils\General::retJson(-1, "Drużyna nie istnieje!"));
    }

    $ids = null;
    if (strstr($_GET["receivers"], "|")) $ids = explode("|", $_GET["receivers"]);
    else $ids = $_GET["receivers"];

    if (is_array($ids) || is_object($ids))
    {
        foreach ($ids as $id)
        {
            $inv = new \Team\Invitation($id);
            $inv->sendInvitation($teamid);
        }
    }
    else
    {
        $inv = new \Team\Invitation($ids);
        $inv->sendInvitation($teamid);
    }

    die(\Utils\General::retJson(1, "Zaproszenia wysłane"));
}
catch (\Exception $e)
{
    if ($e->getCode() == 23000) die(\Utils\General::retJson(0, "Zaproszenie zostało już wysłane!"));
    die(\Utils\General::retJson(-1, "Błąd: " . $e->getMessage()));
}

?>