<?php

$teamid = $_GET["teamid"];
if (!is_numeric($teamid)) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Taka drużyna nie istnieje!", "danger", 2));

try
{
    $team = new \Team\Team($teamid);
    if (!$team->isTeamExists()) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Taka drużyna nie istnieje!", "danger", 2));
    if (!$team->isUserInTeam($_SESSION["userid"])) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Nie należysz do tej drużyny!", "danger", 2));
}
catch (\Exception $e)
{
    throw $e;
}

?>

<div class="row">
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="glyphicon glyphicon-th"> Akcje</i>
            </div>
            <div id="actionbuttons" class="box-body text-center">
                <button type="button" class="btn btn-block btn-primary" disabled="disabled" onclick="redirectAddGame()">Dodaj mecz</button>
                <button type="button" class="btn btn-block btn-primary" disabled="disabled">Wprowadź zmiany</button>
                <button type="button" class="btn btn-block btn-primary" disabled="disabled">Zobacz ogólne podsumowanie</button>
                <button type="button" class="btn btn-block btn-primary" disabled="disabled">Generuj szczegółowy raport PDF</button>
                <button type="button" class="btn btn-block btn-primary" disabled="disabled">Zaproponuj układ na następny mecz</button>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-crosshairs"> Ostatnie 10 meczy</i>
            </div>
            <div class="box-body">
                <table class="table table-bordered textwrap">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Moja drużyna</th>
                            <th>Przeciwnik</th>
                            <th>Data meczu</th>
                            <th>Fragment raportu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $games = null;
                        try
                        {
                            $games = \Team\Game::getLast10UserGames($_SESSION["userid"], $teamid);
                        }
                        catch (\Exception $e)
                        {
                            \Utils\Front::error($e->getMessage());
                        }

                        $counter = 0;
                        if (is_null($games)) echo "<div class='alert alert-warning text-center block-center'>Brak meczy w tej drużynie. Spróbuj dodać nowy!</div>";
                        else
                        {
                            foreach ($games as $game)
                            {
                                $counter++;
                                echo "<tr data-gameid='".$game["game_id"]."' onclick='chooseGame(".$game["game_id"].")'>";
                                echo "<td>".$counter.".</td>";
                                echo "<td>".\Team\Team::getNameById($teamid == $game["game_team1id"] ? $game["game_team1id"] : $game["game_team2id"])."</td>";
                                echo "<td>".\Team\Team::getNameById($teamid == $game["game_team1id"] ? $game["game_team2id"] : $game["game_team1id"])."</td>";
                                echo "<td>".date("d-m-Y H:m", $game["game_date"])."</td>";
                                echo "<td>".(strlen($game["game_generaldesc"]) > 60 ? substr($game["game_generaldesc"], 0, 60)."..." : $game["game_generaldesc"]);
                                echo "</tr>";
                            }
                        }

                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>