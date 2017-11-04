<?php

\Utils\Front::printPageDesc("Drużyny", "Edycja drużyny");

$teamid = $_GET["teamid"];
$userid = (isset($_SESSION["userid"]) ? $_SESSION["userid"] : null);
$team = null;
global $CONF;
if (!isset($teamid) || !is_numeric($teamid))
{
    \Utils\General::redirectWithMessageAndDelay($CONF["site"]."/panel/?tab=teams", "Błąd", "Nie wybrałeś drużyny!", "danger", 2);
    die;
}
else if (is_null($userid))
{
    \Utils\General::redirectWithMessageAndDelay($CONF["site"]."/panel/index.php", "Błąd", "Nie jesteś zalogowany!", "danger", 2);
    die;
}
else
{
    try
    {
        $team = new \Team\Team($teamid);
        if (!$team->isUserLeader($userid))
        {
            \Utils\General::redirectWithMessageAndDelay($CONF["site"]."/panel/?tab=teams", "Uwaga!", "Nie jesteś liderem drużyny!", "warning", 2);
            die;
        }

        if (isset($_POST["teamName"]))
        {
            $data = $team->getTeamInfo(true);
            $dbname = $data["team_name"];
            $dbdesc = $data["team_description"];
            $dbsportid = $data["team_sport"];
            $dbsportname = \Team\Sport::sportIdToName($dbsportid);

            \Utils\Validations::validatePostArray($_POST);
            $formname = \Utils\Validations::validateInput($_POST["teamName"]);
            $formdesc = \Utils\Validations::validateInput($_POST["teamDescription"]);
            $formsport = \Utils\Validations::validateInput($_POST["teamSport"]);

            $team->updateTeamInfo($formname, $formdesc, $formsport);
            \Utils\General::redirectWithMessageAndDelay($CONF["site"]."/panel/?tab=teams", "Powodzenie", "Dane zostały zaaktualizowane!", "success", 1);
        }
    }
    catch (\Exception $e)
    {
        \Utils\Front::error($e->getMessage());
    }
}
?>

<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-bars"> Edytuj drużyne</i>
                <div class="box box-body">
                    <form method="post">
                        <label for="name">Nazwa</label>
                        <input type="text" id="name" class="form-control" maxlength="32" name="teamName" value="<?php echo $team->getTeamInfo(true)["team_name"];?>">
                        <label for="desc">Opis</label>
                        <textarea class="form-control" id="desc" style="resize: none;" maxlength="256" name="teamDescription" placeholder="Wprowadź krótki opis drużyny"><?php echo $team->getTeamInfo(true)["team_description"];?></textarea>
                        <label for="sport">Wybór sportu</label>
                        <select id="sport" class="form-control" name="teamSport">
                            <?php

                            try
                            {
                                $sports = new \Team\Sport();
                                $sports_arr = $sports->getAllSports();
                                foreach ($sports_arr as $sport)
                                {
                                    $selected = false;
                                    if ($sport["sport_name"] == $team->getTeamInfo()["team_sport"]) $selected = true;
                                    echo "<option ".($selected ? "selected='selected'" : "")." value='".$sport["sport_id"]."'>".$sport["sport_name"]."</option>";
                                }
                            }
                            catch (\Exception $e)
                            {
                                \Utils\Front::error($e->getMessage());
                            }

                            ?>
                        </select>
                        <input type="submit" class="btn btn-success center-block" style="width: 25%;" value="Edytuj">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3"></div>
</div>