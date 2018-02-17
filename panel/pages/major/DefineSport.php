<?php

\Utils\Front::printPageDesc("Dyscypliny", "Zarządzanie własnymi dyscyplinami");

try
{
    if (isset($_POST["sport_name"]))
    {
        if (!isset($_POST["sport_name"]) || !isset($_POST["actions"])) throw new \Exception("Uzupełnij wszystkie pola!");
        if (!isset($_SESSION["userid"])) throw new \Exception("Nie jesteś zalogowany!");

        \Utils\Validations::validatePostArray($_POST);
        \Utils\Validations::validateWholeArray($_POST);

        if (!is_array($_POST["actions"])) throw new \Exception("Błąd związany z akcjami. Spróbuj ponownie");
        if (count($_POST["actions"]) < 1 || empty($_POST["actions"]) || (count($_POST["actions"]) == 1 && $_POST["actions"][0] == "")) throw new \Exception("Brakujące akcje");

        $sport = \Team\Sport::createNewSport($_POST["sport_name"], $_SESSION["userid"]);
        $sport->addOrChangeActions($_POST["actions"]);

        \Utils\Front::success("Pomyślnie utworzono dyscyplinę");
    }

    if (isset($_POST["delete"]))
    {
        $sport = new \Team\Sport($_POST["delete"]);
        $ret = $sport->isUserOwner($_SESSION["userid"]);

        if ($ret)
        {
            $sport->deleteSport();
            \Utils\Front::success("Pomyślnie usunięto dyscyplinę");
        }
        else
        {
            \Utils\Front::warning("Nie jesteś autorem tej dyscypliny!");
        }
    }
}
catch (\Exception $e)
{
    \Utils\Front::error($e->getMessage());
}

?>
<script src="../../../js/sports.js"></script>
<div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4><i class="fa fa-plus"></i> Nowa dyscyplina</h4>
                <div class="text-center" style="margin-top: 5px;">
                    <button class="btn btn-success text-center" type="button" onclick="addNewSportAction()" style="width: 49%;">Dodaj kolejną</button>
                    <button class="btn btn-danger text-center" type="button" onclick="deleteLastSportAction()" style="width: 49%;">Usuń ostatnią</button>
                </div>
            </div>
            <div class="box-body">
                <form method="post">
                    <label>Nazwa:</label>
                    <input name="sport_name" style="width: 75%;" title="sport_name">
                    <label>Akcje typowe dla dyscypliny: (jedna w linii)</label>
                    <div class="btn-block" id="sport_actions">
                        <input class="btn-block" type="text" title="custom_action" id="action1" name="actions[]" maxlength="32">
                    </div>
                    <input class="btn btn-success btn-block" type="submit" value="Utwórz">
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4><i class="fa fa-bars"></i> Twoje dyscypliny</h4>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nazwa</th>
                            <th>Edytuj</th>
                            <th>Usuń</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $sports = \Team\Sport::getAllUsersSports($_SESSION["userid"]);
                        if (empty($sports)) echo "<div class='alert alert-warning btn-block'>Nie masz jeszcze własnych dyscyplin</div>";
                        $counter = 0;
                        foreach ($sports as $sport)
                        {
                            $counter++;
                            echo "<tr>";
                            echo "<td>".$counter.".</td>";
                            echo "<td>".$sport["sport_name"]."</td>";
                            echo "<td><button class='btn btn-warning btn-block' onclick='redirectEditSport(".$sport["sport_id"].")'>Edytuj</button></td>";
                            echo "<td><form method='post'><input type='hidden' name='delete' value='".$sport["sport_id"]."'><input type='submit' value='Usuń' class='btn btn-danger btn-block'></form></td>";
                            echo "</tr>";
                        }

                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-1"></div>
    <script>
        $("#teams").addClass("active");
    </script>
</div>