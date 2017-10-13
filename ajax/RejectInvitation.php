<?php

header('Content-Type: application/json'); // json response

$response = array();
if (!isset($_GET["teamid"]) || !is_numeric($_GET["teamid"]))
{
    $response["msg"] = "Błędny identyfikator drużyny!";
    $response["code"] = -1;
    die(json_encode($response, JSON_UNESCAPED_UNICODE));
}

$teamid = $_GET["teamid"];
session_start();

if (!isset($_SESSION["userid"]))
{
    $response["msg"] = "Nie jesteś zalogowany!";
    $response["code"] = -1;
    die(json_encode($response, JSON_UNESCAPED_UNICODE));
}
else
{
    require_once(__DIR__."/../classes/Team/Invitation.php");
    $invitation = new \Team\Invitation($_SESSION["userid"]);
    $ret = $invitation->verifyInvitation($teamid);

    require_once(__DIR__."/../classes/Team/Team.php");
    $team = new \Team\Team($teamid);
    if ($team->isUserInTeam($_SESSION["userid"]))
    {
        $response["msg"] = "Już jesteś w tej drużynie!";
        $response["code"] = 0;
        die(json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    if ($ret)
    {
        try
        {
            $invitation->rejectInvitation($teamid);
            $response["msg"] = "Zaproszenie do drużyny zostało odrzucone";
            $response["code"] = 1;
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
        catch (\Exception $e)
        {
            $response["msg"] = "Nieznany błąd: ".$e->getMessage();
            $response["code"] = -1;
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }
    else
    {
        $response["msg"] = "Nie jesteś zaproszony do tej drużyny!";
        $response["code"] = 0;
        die(json_encode($response, JSON_UNESCAPED_UNICODE));
    }
}
?>