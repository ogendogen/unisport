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
                    <select class="form-control" name="teamSport">
                        <?php

                        try
                        {
                            $sports = new \Team\Sport();
                            /*$sports_arr = $sports->getAllSports();
                            foreach ($sports_arr as $sport)
                            {
                                echo "<option value='".$sport["sport_name"]."'>".$sport["sport_name"]."</option>";
                            }*/
                        }
                        catch (\Exception $e)
                        {
                            throw $e;
                        }

                        ?>
                    </select>
                </form>
            </div>

            <div class="box-body">
                <button class="btn btn-block btn-primary" type="button">Stwórz nową drużynę</button>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-bars"> Akcje na istniejącej drużynie</i>
            </div>

            <div class="box-body">
                <button class="btn btn-block btn-primary" type="button">Edytuj podstawowe dane o drużynie</button>
                <button class="btn btn-block btn-primary" type="button">Dodaj członka do drużyny</button>
                <button class="btn btn-block btn-danger" type="button">Usuń zaznaczoną drużynę</button>
            </div>
        </div>
    </div>

    <div class="col-md-4" style="height: 330px; max-height: 330px;">
        <div class="box box-default pre-scrollable">
            <div class="box-header with-border">
                <i class="fa fa-bars"> Zaproszenia</i>
            </div>

            <div class="box-body">

                <?php

                try
                {
                    $invitation = new \Team\Invitation();
                    $inv_list = $invitation->getAllUserInvitations($_SESSION["userid"]);
                    if (isset($inv_list[0]))
                    {
                        foreach ($inv_list as $inv)
                        {
                            echo "<p>Jesteś zaproszony do drużyny <span style='font-weight: bold;'>".$inv["team_name"]."</span></p>";
                            echo "<button class='btn btn-block btn-success' type='button'>Akceptuj</button> <button class='btn btn-block btn-danger' type='button'>Odrzuć</button>";
                            echo "<div class='pusher'></div>";
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
                <?php

                $team = new \Team\TeamGeneral();
                $userteams = $team->getAllUserTeams($_SESSION["userid"]);
                if (!is_null($userteams[0]["team_name"]))
                {
                    foreach ($userteams as $userteam)
                    {
                        echo "<p>Drużyna ".$userteam["team_name"]."</p>";
                    }
                }
                else
                {
                    // brak drużyn, stwórz jakąś lub dołącz
                    echo "Nie jesteś w żadnej drużynie. Poproś swojego lidera o zaproszenie lub stwórz własną drużynę !";
                }
                ?>
            </div>
        </div>
    </div>
</div>