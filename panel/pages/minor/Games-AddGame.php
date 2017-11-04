<?php

$teamid = $_GET["teamid"];
if (!is_numeric($teamid)) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Taka drużyna nie istnieje!", "danger", 2));
$team = null;

try
{
    $team = new \Team\Team($teamid);
    if (!$team->isTeamExists()) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Taka drużyna nie istnieje!", "danger", 2));
    if (!$team->isUserInTeam($_SESSION["userid"])) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Nie należysz do tej drużyny!", "danger", 2));

    if (isset($_POST)) // coś zostało wysłane!
    {
        \Utils\Validations::validatePostArray($_POST);
        \Utils\Validations::validateWholeArray($_POST);

        //$game = new \Team\Game()
        // todo: add team members picking
    }
}
catch (\Exception $e)
{
    throw $e;
}

?>

<form method="post" id="gameform">
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-5">
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
                            <input id="matchdatetime" name="matchdatetime" type='text' class="form-control" />
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
                            <button type="button" class="btn btn-success text-center" onclick="addNewGameAction()" style="width: 49%;">Dodaj</button>
                            <button type="button" class="btn btn-danger text-center" onclick="deleteLastGame()" style="width: 49%;">Usuń</button>
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

                                            // get list of team members
                                            $team_members = $team->getAllTeamPlayers();
                                            foreach ($team_members as $team_member)
                                            {
                                                echo "<option value='".$team_member["user_id"]."'>".$team_member["user_name"]." ".$team_member["user_surname"]."</option>";
                                            }

                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="actionname1">
                                            <?php

                                            $sport_id= $team->getTeamInfo()["team_sport"];
                                            $sport = new \Team\Sport($sport_id);
                                            $actions = $sport->getAllSportActions();
                                            foreach ($actions as $action)
                                            {
                                                try
                                                {
                                                    $transalated = \Utils\Dictionary::keyToWord($action);
                                                }
                                                catch (\Exception $e)
                                                {
                                                    \Utils\Front::error($e->getMessage());
                                                }

                                                echo "<option value='".$action."'>".$transalated."</option>";
                                            }

                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="actionminute1" pattern="\d*" data-minutesinput="1" value="0" min="0" max="120">
                                    </td>
                                    <td>
                                        <input type="number" name="actionsecond1" pattern="\d*" data-secondsinput="1" value="0" min="0" max="59">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-1"></div>
            <script type="text/javascript">
                $(function () {
                    $('#datetimepicker1').datetimepicker({
                        locale: "pl",
                        maxDate: new Date()
                    });
                    CKEDITOR.replace("gamereport");
                });

                $("#gameform").submit(function(){
                   var form_serialized = $("#gameform").serialize();
                   var inputs = form_serialized.split("&");
                   var i;
                   var input_name;
                   var input_value;
                   var splitted;
                   for (i = 0, len = inputs.length; i < len; i++)
                   {
                       splitted = inputs[i].split("=");
                       input_name = splitted[0];
                       input_value = splitted[1];
                       if (input_value.length === 0)
                       {
                           var message;
                           switch(input_name)
                           {
                               case "opponent_team":
                                   message = "Wybierz przeciwnika!";
                                   break;

                               case "matchdatetime":
                                   message = "Wybierz datę meczu!";
                                   break;

                               case "report":
                                   message = "Uzupełnij raport gry!";
                                   break;
                           }
                           modalWarning("Uwaga!", message);
                           return false;
                       }
                       if (input_name === "matchdatetime")
                       {
                           if (!moment(input_value, "DD.MM.YYYY HH:mm").isValid())
                           {
                               modalWarning("Uwaga!", "Format daty nie jest prawidłowy! Skorzystaj z kalendarza!");
                               return false;
                           }
                           if (moment(input_value, "DD.MM.YYYY HH:mm", true).isAfter(moment.now()))
                           {
                               modalWarning("Uwaga!", "Wybrałeś czas z przyszłości!");
                               return false;
                           }
                       }
                       if (input_value.length === 0)
                       {
                           modalWarning("Uwaga!", "Jedno z pól jest puste!");
                           return false;
                       }
                   }
                   return true;
                });
            </script>
    </div>

    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4 text-center">
            <input type="submit" value="Zakończ dodawanie" class="btn btn-success btn-block">
        </div>
        <div class="col-md-4"></div>
    </div>
</form>