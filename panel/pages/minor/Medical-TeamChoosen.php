<?php

$user = null;
$team = null;

try
{
    if (!isset($_GET["teamid"]) || !isset($_GET["playerid"])) throw new \Exception("Taki gracz nie istnieje!");

    $user = new \User\LoggedUser($_GET["playerid"]);
    $team = new \Team\Team($_GET["teamid"]);

    if (!$team->isUserInTeam($user->getUserId())) throw new \Exception("Ten gracz nie należy do drużyny!");
    if (!$team->isUserInTeam($_SESSION["userid"])) throw new \Exception("Nie należysz do tej drużyny!");
}
catch(\Exception $e)
{
    \Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Uwaga", $e->getMessage(), "danger", 2);
}

try
{
    if (isset($_POST["height"]))
    {
        $user = new \User\LoggedUser($_GET["playerid"]);
        $team = new \Team\Team($_GET["teamid"]);
        if (!$team->isUserLeader($_SESSION["userid"])) throw new \Exception("Nie jesteś liderem!");

        \Utils\Validations::validatePostArray($_POST);
        \Utils\Validations::validateWholeArray($_POST);

        if (intval($_POST["height"]) < 140 || intval($_POST["height"]) > 250) throw new \Exception("Wysokość po za zakresem!");
        if (intval($_POST["weight"]) < 30 || intval($_POST["weight"]) > 200) throw new \Exception("Waga po za zakresem!");
        if (intval($_POST["waist"]) < 30 || intval($_POST["waist"]) > 150) throw new \Exception("Obwód pasa po za zakresem!");

        $medical = new \User\Medical($_GET["playerid"], $_GET["teamid"]);
        $medical->addNewMedicalRecord($_POST["height"], floatval($_POST["weight"]), floatval($_POST["waist"]), $_POST["state"], intval($_POST["iscapable"]));

        \Utils\Front::success("Poprawnie dodano dane");
    }

    if (isset($_POST["id_to_delete"]))
    {
        $user = new \User\LoggedUser($_GET["playerid"]);
        $team = new \Team\Team($_GET["teamid"]);
        if (!$team->isUserLeader($_SESSION["userid"])) throw new \Exception("Nie jesteś liderem!");

        $medical = new \User\Medical($_GET["playerid"], $_GET["teamid"]);
        $medical->deleteRow($_POST["id_to_delete"]);

        \Utils\Front::success("Pomyślnie usunięto wiersz");
    }
}
catch (\Exception $e)
{
    \Utils\Front::error($e->getMessage());
}

?>

<script src="../../../js/medical.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>
<!--<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>-->
<div class="row">
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4><i class="fa fa-plus"></i> Dodaj nowy wpis</h4>
            </div>
            <div class="box-body" onload="countParameters()">
                <form method="post">
                    <label for="height">Wysokość w cm</label>
                    <input type="number" class="btn-block" name="height" id="height" min="140" max="250" step="1" onchange="countParameters()">

                    <label for="weight">Waga w kg</label>
                    <input type="number" class="btn-block" name="weight" id="weight" min="30.00" max="200.00" step="0.1" onchange="countParameters()">

                    <label for="waist">Obwód pasa w cm</label>
                    <input type="number" class="btn-block" name="waist" id="waist" min="30.00" max="150.00" step="0.1" onchange="countParameters()">

                    <label for="state">Ogólny stan zdrowia</label>
                    <select class="btn-block" name="state" id="state">
                        <option value="injured">Skontuzjowany/Chory</option>
                        <option value="bad">Średni</option>
                        <option value="ok" selected>W porządku</option>
                        <option value="very fit">W bardzo dobrej formie</option>
                    </select>

                    <label for="iscapable">Czy zdolny do gry ?</label>
                    <select class="btn-block" name="iscapable" id="iscapable">
                        <option value="1" selected>Tak</option>
                        <option value="0">Nie</option>
                    </select>

                    <label for="bmi">BMI (obliczane automatycznie)</label>
                    <div style="width: 50%; float: left;">
                        <input type="number" class="btn-block" name="bmi" id="bmi" readonly="readonly">
                    </div>
                    <div id="bmiresult" class="alert-info" style="width: 50%; float: left; text-align: center;">
                        Intepretacja BMI
                    </div>

                    <label for="fat">% tkanki tłuszczowej (obliczane automatycznie)</label>
                    <input type="number" class="btn-block" name="fat" id="fat" readonly="readonly">

                    <input type="submit" class="btn btn-block btn-success" value="Wyślij">
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-9" style="max-height: 450px;">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4><i class="fa fa-address-book"></i> Szczegółowe dane</h4>
            </div>
            <div class="box-body pre-scrollable">
                <table class="table table-bordered textwrap">
                    <thead>
                    <tr>
                        <th>No.</th>
                        <th>Wysokość</th>
                        <th>Waga</th>
                        <th>Obwód pasa</th>
                        <th>BMI</th>
                        <th>% Tkanki tłuszczowej</th>
                        <th>Ogólny stan</th>
                        <th>Czy zdolny do gry ?</th>
                        <th>Data</th>
                        <th>Usuń wiersz</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    $medical = new \User\Medical($_GET["playerid"], $_GET["teamid"]);
                    $data = $medical->getAllUserData();
                    if (empty($data)) echo "<div class='alert alert-warning btn-block'>Brak danych</div>";

                    $counter = 0;
                    foreach ($data as $data_row)
                    {
                        $counter++;
                        echo "<tr>";
                        echo "<td>".$counter."</td>";
                        echo "<td>".$data_row["medical_height"]."</td>";
                        echo "<td>".$data_row["medical_weight"]."</td>";
                        echo "<td>".$data_row["medical_waist"]."</td>";
                        echo "<td>".$data_row["medical_bmi"]."</td>";
                        echo "<td>".$data_row["medical_fat"]."</td>";
                        echo "<td>".$data_row["medical_generalhealthstate"]."</td>";
                        echo "<td>".($data_row["medical_iscapable"] == "1" ? "Tak" : "Nie")."</td>";
                        echo "<td>".date("Y-m-d", $data_row["medical_date"])."</td>";
                        echo "
                               <td style='text-align: center;'>
                               <form method='post'>
                                    <button type='submit' style='max-width: 100%;' onclick='return confirm(\"Czy na pewno chcesz usunąć ten wpis ?\");' name='deleteMedical' class='btn btn-danger'>
                                        <i class='btn fa fa-trash'></i>
                                    </button>
                                    <input type='hidden' name='id_to_delete' value='".$data_row["medical_id"]."'>
                               </form>";
                        echo "</tr>";
                    }

                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3" style="min-width: 25%; height: 100%;">
        <canvas id="weight_chart" width="400" height="400"></canvas>
    </div>
    <div class="col-md-3" style="min-width: 25%; height: 100%;">
        <canvas id="waist_chart" width="400" height="400"></canvas>
    </div>
    <div class="col-md-3" style="min-width: 25%; height: 100%;">
        <canvas id="bmi_chart" width="400" height="400"></canvas>
    </div>
    <div class="col-md-3" style="min-width: 25%; height: 100%;">
        <canvas id="fat_chart" width="400" height="400"></canvas>
    </div>
</div>
<script src="../../../js/chartjs.js"></script>