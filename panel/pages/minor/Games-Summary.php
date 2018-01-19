<?php

$teamid = $_GET["teamid"];
$gameid = $_GET["gameid"];
if (!is_numeric($teamid)) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Taka drużyna nie istnieje!", "danger", 2));
if (!is_numeric($gameid)) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Taki mecz nie istnieje!", "danger", 2));
$game = null;
$gamedata = null;
$team = null;

try
{
    $team = new \Team\Team($teamid);
    if (!$team->isTeamExists()) throw new \Exception("Taka drużyna nie istnieje!");
    if (!$team->isUserInTeam($_SESSION["userid"])) throw new \Exception("Nie należysz do tej drużyny!");
    $game = new \Team\Game($_GET["gameid"]);
    if (!$game->isGameExists()) throw new \Exception("Taki mecz nie istnieje!");
    $gamedata = $game->getGameData();
    if (!is_array($gamedata) || count($gamedata) == 0) throw new \Exception("Problem z pobraniem danych meczu");
}
catch (\Exception $e)
{
    \Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", $e->getMessage(), "danger", 2);
}

?>

<div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-10">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-briefcase"> Podstawowe informacje</i>
            </div>
            <div class="box-body">
                <table class="table table-bordered textwrap">
                    <thead>
                        <tr>
                            <th>Twoja drużyna</th>
                            <th>Drużyna przeciwnika</th>
                            <th>Wynik</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php

                            $team1 = new \Team\Team($teamid);
                            $team1_name = $team1->getTeamInfo()["team_name"];

                            $team2 = new \Team\Team($gamedata["game_team1id"]);
                            $team2_name = $team2->getTeamInfo()["team_name"];

                            echo "<td>".$team1_name."</td>";
                            echo "<td>".$team2_name."</td>";
                            echo "<td>".$gamedata["game_team1score"].":".$gamedata["game_team2score"]."</td>";
                            echo "<td>".date("d-m-Y H:m", $gamedata["game_date"])."</td>";

                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-1"></div>
</div>

<div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-pencil-square-o"> Raport</i>
            </div>
            <div class="box-body">
                <?php

                if (strpos($gamedata["game_generaldesc"], "script") !== true)
                {
                    echo html_entity_decode($gamedata["game_generaldesc"]);
                }
                else
                {
                    echo "<div class='alert alert-danger btn-block'>Raport zawiera niedozwolone tagi!</div>";
                }

                ?>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-users"> Uczestnicy</i>
            </div>
            <div class="box-body">
                <ul>
                    <?php

                    // print all players that took part in game
                    // $gamedata["game_team1players"]
                    foreach ($gamedata["game_team1players"] as $playerid)
                    {
                        $player = new \User\LoggedUser($playerid);
                        echo "<li>".$player->getUserCredentials()."</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-sitemap"> Akcje graczy</i>
            </div>
            <div class="box-body">
                <ul>
                    <?php

                    //var_dump($gamedata);

                    //$team = new \Team\Team($gamedata["game_team1id"]);
                    //$team->getTeamInfo()
                    // Gracz (id -> name) wykonał (key -> word) w (m) minucie i (s) sekundzie
                    //echo "<p>Gracz ".</p>";

                    $actions = $game->getAllGameActions();
                    $res = false;
                    foreach ($actions as $action)
                    {
                        if (!in_array(null, $action, true))
                        {
                            $res = true;
                            echo "<li>Gracz <span style='font-weight: bold'>".$action["user_name"]." ".$action["user_surname"]."</span> wykonał akcję <span style='font-weight: bold;'>".strtolower($action["actions_action"])."</span> w minucie <span style='font-weight: bold'>".$action["actions_minute"].":".$action["actions_second"]."</span></li>";
                        }
                    }

                    if (!$res) echo "<div class='alert alert-warning btn-block'>Brak akcji w tym meczu!</div>";

                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-1"></div>
</div>

<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <div class="alert alert-success btn-block text-center cursorhand" onclick="window.history.back()">
            Powrót
        </div>
    </div>
    <div class="col-md-4"></div>
</div>