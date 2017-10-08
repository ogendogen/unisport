<?php

//header('Content-Type: application/json'); // json response

$teamid = $_GET["teamid"];
$response = array();
if (!is_numeric($teamid))
{
    $response["msg"] = "Błędny identyfikator drużyny!";
    $response["code"] = -1;
    die(json_encode($response));
}

session_start();

if (!isset($_SESSION["userid"]))
{
    $response["msg"] = "Nie jesteś zalogowany!";
    $response["code"] = -1;
    die(json_encode($response));
}
else
{
    require_once(__DIR__."/../classes/Team/Invitation.php");
    $invitation = new \Team\Invitation($_SESSION["userid"], $teamid);
    $ret = $invitation->verifyInvitation();

    // todo: czy user nie jest już przypadkiem w drużynie ?!?!
    if ($ret)
    {
        try
        {
            $invitation->acceptInvitation();
            $response["msg"] = "Pomyślnie dołączyłeś do drużyny!";
            $response["code"] = 1;
            die(json_encode($response));
        }
        catch (\Exception $e)
        {
            $response["msg"] = "Nieznany błąd: ".$e->getMessage();
            $response["code"] = -1;
            die(json_encode($response));
        }
    }
    else
    {
        $response["msg"] = "Nie jesteś zaproszony do tej drużyny!";
        $response["code"] = 0;
        die(json_encode($response));
    }
}
?>