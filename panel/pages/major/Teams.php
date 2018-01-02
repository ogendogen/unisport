<?php

\Utils\Front::printPageDesc("Drużyny", "Zarządzanie drużynami");

?>

<div class="row">
    <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-bars"> Nowa drużyna</i>
                <form method="post">
                    <label>Nazwa</label>
                    <input type="text" class="form-control" maxlength="32" name="teamName" placeholder="Wprowadź nazwę drużyny">
                    <label>Opis</label>
                    <textarea class="form-control" style="resize: none;" maxlength="256" name="teamDescription" placeholder="Wprowadź krótki opis drużyny"></textarea>
                    <label>Wybór sportu</label>
                    <select class="form-control" title="team_sport" name="teamSport">
                        <?php

                        try
                        {
                            $sports = new \Team\Sport();
                            $sports_arr = $sports->getAllSports();
                            foreach ($sports_arr as $sport)
                            {
                                echo "<option value='".$sport["sport_id"]."'>".$sport["sport_name"]."</option>";
                            }
                        }
                        catch (\Exception $e)
                        {
                            throw $e;
                        }

                        ?>
                    </select>

                    <div class="box-body">
                        <input type="submit" class="btn btn-block btn-primary" value="Stwórz nową drużynę">
                    </div>
                </form>
            </div>

            <?php

            if (isset($_POST["teamName"]))
            {
                if (!isset($_POST["teamDescription"]) || !isset($_POST["teamSport"]) || empty($_POST["teamDescription"]) || empty($_POST["teamSport"])) \Utils\Front::warning("Uzupełnij wszystkie pola!");
                else
                {
                    try
                    {
                        $newteam = new \Team\Team(0);
                        $teamname = htmlspecialchars(trim(stripslashes($_POST["teamName"])));
                        $teamdesc = htmlspecialchars(trim(stripslashes($_POST["teamDescription"])));
                        $teamsport = htmlspecialchars(trim(stripslashes($_POST["teamSport"])));
                        $newteam->createNewTeam($teamname, $teamdesc, $_SESSION["userid"], $teamsport);
                    }
                    catch (\Exception $e)
                    {
                        if ($e->getCode() == 23000) \Utils\Front::warning("Taka drużyna już istnieje!");
                        else \Utils\Front::warning("Wystąpił problem: ". $e->getMessage());
                    }
                }
            }

            ?>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-bars"> Akcje lidera</i>
            </div>

            <div class="box-body" id="teamLeaderBtns">
                <button class="btn btn-block btn-primary" id="btnEditTeam" disabled="disabled" type="button" onclick="editTeam();">Edytuj podstawowe dane o drużynie</button>
                <button class="btn btn-block btn-primary" id="btnAddMember" disabled="disabled" type="button" onclick="editMembers();">Dodaj/Usuń członków drużyny</button>
                <button class="btn btn-block btn-danger" id="btnDeleteTeam" disabled="disabled" type="button" onclick="deleteTeam();">Usuń zaznaczoną drużynę</button>
            </div>
        </div>
    </div>

    <div class="col-md-4" style="height: 330px; max-height: 330px;">
        <div class="box box-default pre-scrollable">
            <div class="box-header with-border">
                <i class="fa fa-bars"> Zaproszenia</i>
            </div>

            <div class="box-body" id="invbox">

                <?php

                try
                {
                    $invitation = new \Team\Invitation($_SESSION["userid"]);
                    $inv_list = $invitation->getAllUserInvitations();
                    if (isset($inv_list[0]))
                    {
                        foreach ($inv_list as $inv)
                        {
                            echo "<div id='inv".$inv["team_id"]."'>";
                            echo "<p>Jesteś zaproszony do drużyny <span style='font-weight: bold;'>".$inv["team_name"]."</span></p>";
                            echo "<button class='btn btn-block btn-success' type='button' onclick='acceptInvitation(".$inv["team_id"].");'>Akceptuj</button>";
                            echo "<button class='btn btn-block btn-danger' type='button' onclick='rejectInvitation(".$inv["team_id"].");'>Odrzuć</button>";
                            echo "<div class='pusher'></div>";
                            echo "</div>";
                        }
                    }
                    else
                    {
                        echo "Nie jesteś aktualnie zaproszony do żadnej drużyny";
                    }
                }
                catch (\Exception $e)
                {
                    \Utils\Front::error($e->getMessage());
                }

                ?>

            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-default pre-scrollable">
            <div class="box-header with-border">
                <i class="fa fa-bars"> Drużyny</i>
            </div>

            <div class="box-body">
                <table class="table table-bordered">
                    <tbody id="teams_tbody">
                        <tr>
                            <th>#</th>
                            <th>Nazwa drużyny</th>
                            <th>Opis drużyny</th>
                            <th>Dyscyplina sportowa</th>
                        </tr>
                        <?php

                        try
                        {
                            global $logged_user;
                            $userteams = $logged_user->getAllUserTeams();
                            $counter = 0;
                            if (!empty($userteams))
                            {
                                foreach ($userteams as $userteam)
                                {
                                    echo "<tr id='team".$userteam["team_id"]."'>";
                                    echo "<td onclick='chooseTeam(".$userteam["team_id"].");'>".++$counter.".</td>";
                                    $counter--;
                                    echo "<td onclick='chooseTeam(".$userteam["team_id"].");'>".$userteam["team_name"]."</td>";
                                    echo "<td onclick='chooseTeam(".$userteam["team_id"].");'>".$userteam["team_description"]."</td>";
                                    echo "<td onclick='chooseTeam(".$userteam["team_id"].");'>".\Team\Sport::sportIdToName($userteam["team_sport"])."</td>";
                                    echo "</tr>";
                                    $counter++;
                                }
                            }
                            else
                            {
                                echo "<div class='alert alert-info btn-block' id='noteam_alert'>Nie jesteś w żadnej drużynie. Poproś swojego lidera o zaproszenie lub stwórz własną drużynę !</div>";
                            }
                        }
                        catch (\Exception $e)
                        {
                            \Utils\Front::error($e->getMessage());
                        }

                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>