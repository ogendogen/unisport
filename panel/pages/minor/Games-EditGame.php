<?php

if (!isset($_GET["gameid"]) || !is_numeric($_GET["gameid"])) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Taki mecz nie istnieje!", "danger", 2));
$gameid = $_GET["gameid"];
$team = null;
$teamid = null;
$gamedata = null;

try
{
    $game = new \Team\Game($gameid);
    $gamedata = $game->getGameData();
    $teamid = $gamedata["game_team1id"];

    $team = new \Team\Team($teamid);
    if (!$team->isTeamExists()) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Taka drużyna nie istnieje!", "danger", 2));
    if (!$team->isUserInTeam($_SESSION["userid"])) die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd!", "Nie należysz do tej drużyny!", "danger", 2));

    if (isset($_POST) && !empty($_POST)) // coś zostało wysłane!
    {
        \Utils\Validations::validatePostArray($_POST);
        \Utils\Validations::validateWholeArray($_POST);
        if (isset($_POST["blocker"])) throw new \Exception("Nie masz żadnych zawodników w drużynie!");

        $opponent_team_id = $_POST["opponent_team"];
        $opponent_team = new \Team\Team($opponent_team_id);
        if ($opponent_team->getTeamInfo()["team_sport"] != $team->getTeamInfo()["team_sport"]) throw new \Exception("Przeciwnik nie gra w tę samą grę!");
        if ($opponent_team_id == $teamid) throw new \Exception("Nie możesz zagrać z samym sobą!");

        $date = $_POST["matchdatetime"];
        if (!\Utils\Validations::isDateValid($date, "d.m.Y H:i")) throw new \Exception("Format daty jest niepoprawny!");
        $date_timestamp = strtotime($date);
        if ($date_timestamp > time()) throw new \Exception("Taka data jeszcze nie nastąpiła!");

        $players_choosen = array();
        $team_players = $team->getAllTeamPlayers();
        $members_quantity = count($team_players);
        for ($i = 1; $i <= $members_quantity; $i++)
        {
            if (!isset($_POST["player" . $i])) break;
            array_push($players_choosen, $_POST["player" . $i]);
        }

        $team_players_ids = array();
        foreach ($team_players as $team_player) array_push($team_players_ids, $team_player["user_id"]);
        $res = array_intersect($players_choosen, $team_players_ids);
        sort($res);
        sort($players_choosen);

        if ($res != $players_choosen) throw new \Exception("Przynajmniej jeden z wybranych graczy nie gra w tej drużynie!");
        $report = $_POST["report"];
        // todo: zapis raportu w postaci base64 (???)

        $team1_score = intval($_POST["team1score"]);
        $team2_score = intval($_POST["team2score"]);

        $game->editBasicData($opponent_team_id, $date_timestamp, $team1_score, $team2_score, $report);
        $game->editGamePlayers($players_choosen);

        $actions = array();
        $postsize = count($_POST);
        for ($i = 1; $i <= $postsize; $i++)
        {
            if (!isset($_POST["playername" . $i])) break;

            $action = array();
            $action["player_id"] = $_POST["playername" . $i];
            $action["action_name"] = $_POST["actionname" . $i];
            $action["action_minute"] = $_POST["actionminute" . $i];
            $action["action_second"] = $_POST["actionsecond" . $i];

            array_push($actions, $action);
        }

        $game->editGameActions($actions);
        \Utils\Front::success("Mecz został poprawnie edytowany");
    }
}
catch (\Exception $e)
{
    \Utils\Front::error($e->getMessage());
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
                            echo "<option value='".$matched_team["team_id"]."' ". ($matched_team["team_id"] == $gamedata["game_team1id"] ? "selected='selected'" : "") .">".$matched_team["team_name"]."</option>";
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

                    <label for="scorepicking">Wynik meczu</label>
                    <div id="scorepicking" class="form-group">
                        <div class="spinbox" data-min="0" data-max="999" data-step="1">
                            <input class="form-control spinbox-input" name="team1score" type="text" pattern="\d*" value="0">
                            <div class="spinbox-buttons">
                                <button class="spinbox-up btn btn-default btn-xs alert-success" type="button">+</button>
                                <button class="spinbox-down btn btn-default btn-xs alert-danger" type="button">-</button>
                            </div>
                        </div>

                        <div class="spinbox" data-min="0" data-max="999" data-step="1">
                            <input class="form-control spinbox-input" name="team2score" type="text" pattern="\d*" value="0">
                            <div class="spinbox-buttons">
                                <button class="spinbox-up btn btn-default btn-xs alert-success" type="button">+</button>
                                <button class="spinbox-down btn btn-default btn-xs alert-danger" type="button">-</button>
                            </div>
                        </div>
                        <br>
                    </div>

                    <label for="playerspicking">Wskaż zawodników biorących udział</label>
                    <div class="form-group">
                        <?php

                        $players = $team->getAllTeamPlayers();
                        $players_num = count($players);
                        if ($players_num == 0)
                        {
                            echo "<div class='alert alert-danger alert-block'>W drużynie nie ma zawodników!</div>";
                            echo "<input type='hidden' name='blocker' value=''>"; // zapobiega wysłaniu formularza
                        }
                        else
                        {
                            $counter = 1;
                            foreach ($players as $player)
                            {
                                if ($counter % 3 == 0)
                                {
                                    echo "</div>";
                                    $counter = 1;
                                }
                                if ($counter == 1)
                                {
                                    echo "<div class='row'>";
                                }
                                echo "<div class='alert alert-info col-md-4' style='margin-left: 5px;' onclick='choosePlayer(".$player["user_id"].")' data-playerid='".$player["user_id"]."'>".$player["user_name"]." ".$player["user_surname"]."</div>";
                                $counter++;
                            }
                            echo "</div>";
                        }

                        ?>
                    </div>
                    <div id="players2sent" style="display: none;">

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
                                <select name="playername1" class="btn-block">

                                </select>
                            </td>
                            <td>
                                <select name="actionname1" class="btn-block">
                                    <?php

                                    $sport_id= $team->getTeamInfo()["team_sport"];
                                    $sport = new \Team\Sport($sport_id);
                                    $actions = $sport->getAllSportActions();
                                    foreach ($actions as $action)
                                    {
                                        try
                                        {
                                            $transalated = \Utils\Dictionary::keyToWord($action);
                                            if ($transalated == $action)
                                            {
                                                \Utils\Front::error("Problem z tłumaczeniem");
                                                break;
                                            }
                                            echo "<option value='".$action."'>".$transalated."</option>";
                                        }
                                        catch (\Exception $e)
                                        {
                                            \Utils\Front::error($e->getMessage());
                                        }
                                    }

                                    ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" class="btn-block" name="actionminute1" pattern="\d*" data-minutesinput="1" value="0" min="0" max="120">
                            </td>
                            <td>
                                <input type="number" class="btn-block" name="actionsecond1" pattern="\d*" data-secondsinput="1" value="0" min="0" max="59">
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

            $(document).on('click', '.spinbox-up, .spinbox-down', function() {
                var $spinbox = $(this).closest('.spinbox');
                if ($spinbox.length) {
                    var $input = $spinbox.find('input.spinbox-input');
                    if ($input.length) {
                        var max = parseInt($spinbox.data('max')) || false;
                        var min = parseInt($spinbox.data('min')) || false;
                        var val = parseInt($input.val()) || min || 0;
                        var sign = $(this).hasClass('spinbox-up') ? 1 : -1;
                        val += sign * (parseInt($spinbox.data('step')) || 1);
                        if (max && val > max) {
                            val = max;
                        } else if (min && val < min) {
                            val = min;
                        }
                        if (val >= 0) $input.val(val).trigger('change');
                    }
                }
            });

            $("#gameform").submit(function(){
                var form_serialized = $("#gameform").serialize();
                var inputs = form_serialized.split("&");
                var i;
                var input_name;
                var input_value;
                var splitted;

                if ($("#players2sent").children().length === 0)
                {
                    modalWarning("Uwaga!", "Nie wybrałeś żadnego zawodnika!");
                    return false;
                }

                for (i = 0, len = inputs.length; i < len; i++)
                {
                    splitted = inputs[i].split("=");
                    input_name = splitted[0];
                    input_value = splitted[1];

                    if (input_name === "report")
                    {
                        input_value = CKEDITOR.instances["gamereport"].getData();
                        if (input_value.length === 0)
                        {
                            modalWarning("Uwaga!", "Uzupełnij raport gry!");
                            return false;
                        }
                    }

                    if (input_name === "blocker")
                    {
                        modalWarning("Uwaga!", "W drużynie brakuje zawodników!");
                        return false;
                    }

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

                            default:
                                message = "Uzupełnij pole " + input_name;
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