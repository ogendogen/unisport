<?php

session_start();
header('Content-Type: application/json');
require_once(__DIR__."/../classes/Utils/TCPDF/tcpdf.php");
require_once(__DIR__."/../classes/Utils/General.php");
require_once(__DIR__."/../classes/Team/Game.php");
require_once(__DIR__."/../classes/Utils/Dictionary.php");

if (!isset($_SESSION["userid"]) || !isset($_SESSION["userid"])) die(\Utils\General::retJson(-1, "Nie jesteś zalogowany!"));
if (!isset($_GET["gameid"]) || !is_numeric($_GET["gameid"])) die(\Utils\General::retJson(-1, "Taka gra nie istnieje!"));

try
{
    $game = new \Team\Game($_GET["gameid"]);
    $teamid = $game->getGameData()["game_team1id"];
    $team = new \Team\Team($teamid);
    $players = $team->getAllTeamPlayers();

    $found = false;
    foreach ($players as $player)
    {
        if (in_array($_SESSION["userid"], $player)) $found = true;
    }
    if (!$found) throw new Exception("Nie należysz do tej drużyny!");

    $gamedata = $game->getGameData();
    $actions = $game->getAllGameActions();
    $teaminfo = $team->getTeamInfo();

    $team2 = new \Team\Team($gamedata["game_team2id"]);
    $team2info = $team->getTeamInfo();

    // PDF
    $pdf = \Utils\General::preparePDF($_GET["gameid"]);
    $pdf->SetFont('dejavusans', '', 14, '', true);
    $pdf->AddPage();
    $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

    // Właściwe budowanie PDF'a
    $html = "<span style='text-align: center'><h1>".$teaminfo["team_name"]." przeciwko ". $team2info["team_name"]."</h1></span>";
    $pdf->SetY(10);
    $pdf->SetX(25);
    $pdf->writeHTMLCell(250, 0, '', '', $html, 0, 1, 0, true, '', true);

    $html = "<p>Data: ".date("d-m-Y H:m", $gamedata["game_date"])."</p>";
    $pdf->writeHTMLCell(250, 0, '', '', $html, 0, 1, 0, true, '', true);

    $score1 = intval($gamedata["game_team1score"]);
    $score2 = intval($gamedata["game_team2score"]);

    if ($score1 > $score2) $html = "<p>Wynik: <span style='color: lime;'>".$score1."</span>:<span style='color: red;'>".$score2."</span></p>";
    else if ($score1 == $score2) $html = "<p>Wynik: <span style='color: #ff0000;'>".$score1."</span>:<span style='color: #00ffff;'>".$score2."</span></p>";
    else $html = "<p>Wynik: <span style='color: #f59e00;'>".$score1."</span>:<span style='color: #f59e00;'>".$score2."</span></p>";
    $pdf->writeHTMLCell(250, 0, '', '', $html, 0, 1, 0, true, '', true);

    $html = "";
    $actions = $game->getAllGameActions();
    $res = false;
    if ($team->isFootballTeam())
    {
        foreach ($actions as $action)
        {
            if (!in_array(null, $action))
            {
                $res = true;
                $html .= "<p>Gracz <span style='font-weight: bold'>".$action["user_name"]." ".$action["user_surname"]."</span> wykonał akcję <span style='font-weight: bold;'>".strtolower(\Utils\Dictionary::keyToWord($action["football_action"]))."</span> w minucie <span style='font-weight: bold'>".$action["football_minute"].":".$action["football_second"]."</span></p>";
            }
        }
    }
    else
    {
        foreach ($actions as $action)
        {
            if (!in_array(null, $action))
            {
                $res = true;
                $html .= "<p>Gracz ".$action["user_name"]." ".$action["user_surname"]." wykonał akcję ".strtolower(\Utils\Dictionary::keyToWord($action["general_action"]))." w minucie ".$action["general_minute"].":".$action["general_second"]."</p>";
            }
        }
    }
    if (!$res) $html = "<p>Brak akcji w tym meczu!</p>";
    $pdf->writeHTMLCell(250, 0, '', '', $html, 0, 1, 0, true, '', true);

    $html = "<p>Raport z meczu: ".html_entity_decode($gamedata["game_generaldesc"])."</p>";
    $pdf->writeHTMLCell(250, 0, '', '', $html, 0, 1, 0, true, '', true);

    $pdf->Output("podsumowanie_meczu".$_GET["gameid"].".pdf", "I");
}
catch (Exception $e)
{
    echo \Utils\General::retJson(0, $e->getMessage());
}

?>