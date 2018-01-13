<?php

require_once(__DIR__."/../classes/Utils/General.php");
require_once(__DIR__."/../classes/User/LoggedUser.php");
require_once(__DIR__."/../classes/Team/Team.php");
require_once(__DIR__."/../classes/User/Medical.php");
session_start();
header('Content-Type: application/json');
try
{
    if (!isset($_SESSION["userid"])) throw new \Exception("Nie jesteś zalogowany");
    if (!isset($_GET["playerid"]) || !isset($_GET["teamid"])) throw new \Exception("Brakujące parametry");
    $user = new \User\LoggedUser($_GET["playerid"]);
    $team = new \Team\Team($_GET["teamid"]);
    if (!$team->isUserInTeam($_GET["playerid"])) throw new \Exception("Zawodnik nie jest w tej drużynie!");

    $medical = new \User\Medical($_GET["playerid"], $_GET["teamid"]);
    $raw_data = $medical->getAllUserData(); // todo: transform array to morris chart format...

    $data = array();
    $data["weight"] = array();
    $data["waist"] = array();
    $data["bmi"] = array();
    $data["fat"] = array();
    $data["date"] = array();
    foreach ($raw_data as $data_row)
    {
        array_push($data["weight"], $data_row["medical_weight"]);
        array_push($data["waist"], $data_row["medical_waist"]);
        array_push($data["bmi"], $data_row["medical_bmi"]);
        array_push($data["fat"], $data_row["medical_fat"]);
        array_push($data["date"], date("Y-m-d", $data_row["medical_date"]));
    }

    echo \Utils\General::retJsonArray(1, $data);
}
catch (\Exception $e)
{
    echo \Utils\General::retJson(-1, $e->getMessage());
}

?>