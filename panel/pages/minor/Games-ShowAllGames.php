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
<img style="cursor: pointer;" onclick="window.history.back()" src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjY0cHgiIGhlaWdodD0iNjRweCIgdmlld0JveD0iMCAwIDMxNC4wNjkgMzE0LjA2OSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMzE0LjA2OSAzMTQuMDY5OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPGcgaWQ9Il94MzRfOTMuX0JhY2siPgoJCTxnPgoJCQk8cGF0aCBkPSJNMjkzLjAwNCw3OC41MjVDMjQ5LjY0LDMuNDM2LDE1My42Mi0yMi4yOTUsNzguNTMxLDIxLjA2MUMzLjQzNiw2NC40MTEtMjIuMjk2LDE2MC40NDMsMjEuMDY4LDIzNS41NDIgICAgIGM0My4zNSw3NS4wODcsMTM5LjM3NSwxMDAuODIyLDIxNC40NjUsNTcuNDY3QzMxMC42MjksMjQ5LjY0OCwzMzYuMzY1LDE1My42MjEsMjkzLjAwNCw3OC41MjV6IE0yMTkuODM2LDI2NS44MDIgICAgIGMtNjAuMDc1LDM0LjY4NS0xMzYuODk0LDE0LjExNC0xNzEuNTc2LTQ1Ljk2OUMxMy41NywxNTkuNzYyLDM0LjE1NSw4Mi45MzYsOTQuMjMyLDQ4LjI1MyAgICAgYzYwLjA3MS0zNC42ODMsMTM2Ljg5NC0xNC4wOTksMTcxLjU3OCw0NS45NzlDMzAwLjQ5NSwxNTQuMzA4LDI3OS45MDgsMjMxLjExOCwyMTkuODM2LDI2NS44MDJ6IE0yMTEuOTg2LDE0MS4zMjhoLTY1LjQ5MSAgICAgbDE3LjU5OS0xNy42MDNjNi4xMjQtNi4xMjksNi4xMjQtMTYuMDc2LDAtMjIuMTk3Yy02LjEyOS02LjEzMy0xNi4wNzgtNi4xMzMtMjIuMjA3LDBsLTQ0LjQwMiw0NC40ICAgICBjLTYuMTI5LDYuMTMxLTYuMTI5LDE2LjA3OCwwLDIyLjIxM2w0NC40MDIsNDQuNDAyYzYuMTI5LDYuMTI4LDE2LjA3OCw2LjEyOCwyMi4yMDcsMGM2LjEyNC02LjEzMSw2LjEyNC0xNi4wNzcsMC0yMi4yMDEgICAgIGwtMTcuNjA2LTE3LjYwMWg2NS40OTljOC42NjksMCwxNS42OTctNy4wNDEsMTUuNjk3LTE1LjcwMXYtMC4wMDhDMjI3LjY4MywxNDguMzUzLDIyMC42NTUsMTQxLjMyOCwyMTEuOTg2LDE0MS4zMjh6IiBmaWxsPSIjRDgwMDI3Ii8+CgkJPC9nPgoJPC9nPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+Cjwvc3ZnPgo=" />
<p></p>
<div class="col-md-12">
    <div class="box box-default">
        <div class="box-header with-border">
            <i class="fa fa-crosshairs"> Wszystkie mecze</i>
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
                <tbody id="tbody_games">
                <?php

                $games = null;
                try
                {
                    $games = \Team\Game::getAllUserGames($_SESSION["userid"], $teamid);
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
                        $desc = (strlen($game["game_generaldesc"]) > 60 ? substr($game["game_generaldesc"], 0, 60)."..." : $game["game_generaldesc"]);
                        echo "<td>". strip_tags(html_entity_decode($desc))."</td>";
                        echo "</tr>";
                    }
                }

                ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
