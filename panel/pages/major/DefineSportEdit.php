<?php

\Utils\Front::printPageDesc("Dyscypliny", "Zarządzanie własnymi dyscyplinami");

$sport = null;

try
{
    if (!isset($_GET["sportid"])) throw new \Exception("Niezdefiniowana drużyna");
    $sport = new \Team\Sport($_GET["sportid"]);
    if (isset($_POST["sport_name"]))
    {
        if (!isset($_POST["sport_name"]) || !isset($_POST["actions"])) throw new \Exception("Uzupełnij wszystkie pola!");
        if (!isset($_SESSION["userid"])) throw new \Exception("Nie jesteś zalogowany!");

        \Utils\Validations::validatePostArray($_POST);
        \Utils\Validations::validateWholeArray($_POST);

        if (!is_array($_POST["actions"])) throw new \Exception("Błąd związany z akcjami. Spróbuj ponownie");
        if (count($_POST["actions"]) < 1 || empty($_POST["actions"]) || (count($_POST["actions"]) == 1 && $_POST["actions"][0] == "")) throw new \Exception("Brakujące akcje");

        $sport->changeName($_POST["sport_name"]);
        $sport->addOrChangeActions($_POST["actions"]);

        \Utils\Front::success("Pomyślnie edytowano dyscyplinę");
    }
}
catch (\Exception $e)
{
    \Utils\Front::error($e->getMessage());
}

?>
<script src="../../../js/sports.js"></script>
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
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
                    <input name="sport_name" style="width: 75%;" title="sport_name" value="<?php echo $sport->getSportName(); ?>">
                    <label>Akcje typowe dla dyscypliny: (jedna w linii)</label>
                    <div class="btn-block" id="sport_actions">
                        <?php

                        $actions = $sport->getAllSportActions();
                        $counter = 0;
                        foreach ($actions as $action)
                        {
                            $counter++;
                            echo "<input class='btn-block' type='text' title='custom_action' id='action".$counter."' name='actions[]' maxlength='32' value='".$action."'>";
                        }

                        ?>
                    </div>
                    <button type="button" class="btn btn-info" style="width: 49%;" onclick="sportBack()">Powrót</button> <input class="btn btn-success" type="submit" style="width: 49%; float: right;" value="Edytuj">
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-3"></div>
    <script>
        $("#teams").addClass("active");
    </script>
</div>