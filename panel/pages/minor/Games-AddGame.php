<?php

$teamid = $_GET["teamid"];
if (!is_numeric($teamid)) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Taka drużyna nie istnieje!", "danger", 2));
$team = null;

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
    <div class="col-md-1"></div>
    <div class="col-md-5">
        <form method="post">
            <div class="box box-default">
                <div class="box-header with-border">
                    <i class="fa fa-comment"> Podstawowe informacje</i>
                </div>
                <div id="basicinfo" class="box-body text-center">
                    <label for="opponentselect">Wybierz drużynę przeciwnika</label>
                    <select class="form-control" id="opponentselect" name="opponent_team">
                        <?php

                        $matched_teams = \Team\Team::getAllTeamsBySport($team->getTeamInfo()["team_sport"]);
                        foreach ($matched_teams as $matched_team)
                        {
                            if ($matched_team["team_id"] == $teamid) continue;
                            echo "<option value='".$matched_team["team_id"]."'>".$matched_team["team_name"]."</option>";
                        }

                        ?>
                    </select>

                    <label for="matchdatetime">Wybierz datę meczu</label>
                    <div class="form-group">
                        <div class='input-group date' id='datetimepicker1'>
                            <input id="matchdatetime" type='text' class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>

                    <label for="gamereport">Wpisz ogólny raport z meczu</label>
                    <textarea class="form-control" name="report" id="gamereport"></textarea>
                </div>
            </div>
            </div>
            <div class="col-md-5">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <i class="fa fa-star"> Dodatkowe informacje</i>
                        <div class="text-center" style="margin-top: 5px;">
                            <button type="button" class="btn btn-success text-center" style="width: 49%;">Dodaj</button>
                            <button type="button" class="btn btn-danger text-center" style="width: 49%;">Usuń</button>
                        </div>
                    </div>
                    <div id="advancedinfo" class="box-body text-center">
                        <table id="actions" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Zawodnik</th>
                                    <th>Akcja</th>
                                    <th>Minuta</th>
                                    <th>Sekunda</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="action1">
                                    <td>
                                        <select name="playername1">
                                            <?php

                                            // get list of team members by ajax

                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="actionname1">
                                            <?php

                                            // get list of transalated actions by ajax

                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="actionminute1" pattern="\d*" data-minutesinput="1" placeholder="0" min="0" max="120">
                                    </td>
                                    <td>
                                        <input type="number" name="actionsecond1" pattern="\d*" data-secondsinput="1" placeholder="0" min="0" max="59">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-1"></div>
        </form>
    <script type="text/javascript">
        $(function () {
            $('#datetimepicker1').datetimepicker({
                locale: "pl",
                maxDate: new Date()
            });
            CKEDITOR.replace("gamereport");
        });
    </script>
</div>