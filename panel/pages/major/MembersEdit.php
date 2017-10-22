<?php

\Utils\Front::printPageDesc("Drużyny", "Zarządzanie zawodnikami");

$teamid = $_GET["teamid"];
$userid = (isset($_SESSION["userid"]) ? $_SESSION["userid"] : null);
$team = null;
global $CONF;
$found_members = null;

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
            \Utils\General::redirectWithMessageAndDelay($CONF["site"]."/panel/?tab=teams", "Uwaga!", "Nie jesteś liderem tej drużyny!", "warning", 2);
            die;
        }

        if (isset($_GET["memberName"]))
        {
            $data = $team->getTeamInfo(true);
            $dbname = $data["team_name"];
            $dbdesc = $data["team_description"];
            $dbsportid = $data["team_sport"];
            $dbsportname = \Team\Sport::sportIdToName($dbsportid);

            $formname = \Utils\General::validateInput($_GET["memberName"]);
            $formsurname = \Utils\General::validateInput($_GET["memberSurname"]);

            global $logged_user;
            $found_members = array();

            $raw_found_members = $logged_user->findUsersByCredentials($formname, $formsurname);
            foreach ($raw_found_members as $found_member)
            {
                if (!$team->isUserInTeam($found_member->getUserId()) && $found_member->getUserId() != $_SESSION["userid"])
                {
                    array_push($found_members, $found_member);
                }
            }
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
                <i class="fa fa-search"> Wyszukaj nowych członków</i>
                <div class="box box-body">
                    <form method="get">
                        Imię: <input type="text" title="membername" class="form-control" maxlength="32" name="memberName" placeholder="Podaj imię i/lub nazwisko" <?php if (isset($_GET["memberName"])) echo "value='".$_GET["memberName"]."'"; ?> >
                        Nazwisko: <input type="text" title="membersurname" class="form-control" maxlength="32" name="memberSurname" <?php if (isset($_GET["memberSurname"])) echo "value='".$_GET["memberSurname"]."'"; ?>>
                        <input type="hidden" name="tab" value="membersedit">
                        <input type="hidden" name="teamid" value="<?php echo $_GET["teamid"]; ?>">
                        <input type="submit" class="btn btn-success center-block" style="width: 25%; min-width: 60px;" value="Szukaj">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3"></div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-users"> Wyszukani zawodnicy</i>
                <div class="box box-body">
                    <table id="foundmembers" class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>#</th>
                                <th>Imię</th>
                                <th>Nazwisko</th>
                            </tr>
                            <?php

                            $counter = 1;
                            if (!empty($found_members))
                            {
                                foreach ($found_members as $found_member)
                                {
                                    $credentials = $found_member->getUserCredentials();
                                    $memberid = $found_member->getUserId();
                                    $name = explode(" ", $credentials)[0];
                                    $surname = explode(" ", $credentials)[1];
                                    echo "<tr id='".$memberid."' onclick='chooseMember(".$memberid.")'>";
                                    echo "<td>".$counter.".</td>";
                                    echo "<td>".$name."</td>";
                                    echo "<td>".$surname."</td>";
                                    echo "<tr>";
                                    $counter++;
                                }
                            }

                            ?>
                        </tbody>
                    </table>
                    <?php

                    if (is_null($found_members))
                    {
                        echo "<div class='alert alert-info text-center'>Wyszukaj zawodników wyżej</div>";
                    }
                    else if (is_array($found_members) && empty($found_members))
                    {
                        echo "<div class='alert alert-warning text-center'>Nie znaleziono zawodników z takimi danymi lub są już w twojej drużynie!</div>";
                    }
                    else if (!empty($found_members))
                    {
                        echo "<button type='button' id='sendinv' class='btn btn-success text-center center-block' onclick='sendInvitation()'>Zaproś zaznaczonych zawodników</button>";
                    }

                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-users"> Twoi zawodnicy</i>
                <div class="box box-body">
                    <table id="members2delete" class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>#</th>
                                <th>Imię</th>
                                <th>Nazwisko</th>
                            </tr>

                            <?php

                            if ($team->countMembers() > 0)
                            {
                                $players = $team->getAllTeamPlayers();
                                $counter = 1;
                                foreach ($players as $player)
                                {
                                    if ($team->isUserLeader($player["user_id"])) echo "<tr data-deleteid='".$player["user_id"]."'>";
                                    else echo "<tr data-deleteid='".$player["user_id"]."' onclick='chooseMemberToDelete(".$player["user_id"].");'>";
                                    echo "<td>".$counter.".</td>";
                                    echo "<td>".$player["user_name"]."</td>";
                                    echo "<td>".$player["user_surname"]."</td>";
                                    echo "</tr>";
                                    $counter++;
                                }
                            }

                            ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-danger text-center center-block" onclick="removeMember()">Usuń zaznaczonych członków</button>
                </div>
            </div>
        </div>
    </div>
</div>