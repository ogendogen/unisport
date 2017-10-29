<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-users"> Wybierz drużynę</i>
            </div>
            <div class="box-body">
                <form method="get" action="?tab=games">
                    <select class="form-control" name="teamid">
                        <?php

                        global $logged_user;
                        $userteams = $logged_user->getAllUserTeams();
                        if (!$userteams)
                        {
                            echo "<div class='alert alert-warning center-block text-center'>Nie należysz do żadnej drużyny!</div>";
                        }
                        else
                        {
                            foreach ($userteams as $team)
                            {
                                echo "<option value='".$team["team_id"]."'>".$team["team_name"]."</option>";
                            }
                            echo "<input type='hidden' name='tab' value='games'>";
                        }

                        ?>
                    </select>
                    <input type="submit" class="btn btn-primary center-block text-center" value="Wybierz">
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-3"></div>
</div>