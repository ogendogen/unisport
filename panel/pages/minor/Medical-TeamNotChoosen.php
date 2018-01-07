<script src="../../../js/medical.js"></script>
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-users"> Wybierz drużynę i gracza</i>
            </div>
            <div class="box-body">
                <form method="get">
                    <select title="team" class="form-control" name="teamid" onchange="getPlayersList(this)">
                        <?php

                        global $logged_user;
                        $userteams = $logged_user->getAllUserTeams();
                        if (!$userteams)
                        {
                            echo "<div class='alert alert-warning center-block text-center'>Nie należysz do żadnej drużyny!</div>";
                        }
                        else
                        {
                            echo "<option value disabled selected style='display: none;'>Wybór drużyny...</option>";
                            foreach ($userteams as $team)
                            {
                                echo "<option value='".$team["team_id"]."'>".$team["team_name"]."</option>";
                            }
                            echo "<input type='hidden' name='tab' value='medical'>";
                        }

                        ?>
                    </select>
                    <p></p>
                    <select title="players" id="players" class="form-control" name="playerid">
                        <option disabled selected>Wybierz najpierw drużynę...</option>
                    </select>

                    <input type="submit" class="btn btn-primary center-block text-center" value="Wybierz">
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-3"></div>
</div>