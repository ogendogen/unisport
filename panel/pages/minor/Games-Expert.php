<?php

$team = null;

try
{
    if (!isset($_GET["teamid"])) throw new \Exception("Taka drużyna nie istnieje!");
    $team = new \Team\Team($_GET["teamid"]);
    if (!$team->isFootballTeam()) throw new \Exception("Opcja dostępna tylko dla drużyn piłki nożnej!");
}
catch (\Exception $e)
{
    die(\Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Błąd", $e->getMessage(), "danger", 2));
}

?>

<div class="row">
    <div class="col-md-3">
        <div class="box box-default">
            <div class="alert btn-block alert-warning">Wyniki analizy zależą od danych podanych w module meczy</div>
            <div class="box-header with-border">
                <i class="fa fa-address-book"> Wybór zawodników</i>
            </div>
            <div class="box-body">
                <form method="post">
                    <p>Wybierz bramkarza:
                    <select name="goalkeeper" title="goalkeeper">
                        <?php

                        $players = $team->getAllTeamPlayers();
                        foreach ($players as $player)
                        {
                            echo "<option value='".$player["user_id"]."'> ".$player["user_name"]." ".$player["user_surname"]."</option>";
                        }

                        ?>
                    </select></p>
                    <p>Wybierz graczy rezerwowych:</p>
                    <?php

                    foreach ($players as $player)
                    {
                        echo "<p><input type='checkbox' name='reserved[]' value='".$player["user_id"]."'>".$player["user_name"]." ".$player["user_surname"]."</p>";
                    }

                    ?>
                    <input type="submit" class="btn btn-block btn-success" value="Analizuj">
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-bar-chart"> Wynik</i>
            </div>
            <div class="box-body">
                <table class="table table-bordered" style="text-align: center;">
                    <tr style="font-weight: bold;">
                        <td>No.</td>
                        <td>Imię i nazwisko</td>
                        <td>Pozycja</td>
                        <td>Dlaczego ?</td>
                        <td>Jak ?</td>
                        <td>Fakty</td>
                    </tr>
                    <?php

                    if (isset($_POST["goalkeeper"]))
                    {
                        try
                        {
                            \Utils\Validations::validateWholeArray($_POST);
                            \Utils\Validations::validatePostArray($_POST);

                            $players_amount = count($team->getAllTeamPlayers());
                            $expert = new \Expert\FootballExpert($_GET["teamid"]);
                            $ret = $expert->doAnalyse($_POST["goalkeeper"], $_POST["reserved"] ?? null); // equivalent to (isset($_POST["reserved"]) ? $_POST["reserved"] : null)
                            $counter = 0;

                            foreach ($ret as $result)
                            {
                                $counter++;
                                echo "<tr>";
                                echo "<td>".$counter."</td>";
                                echo "<td>".$result["credentials"]."</td>";
                                echo "<td>".$result["player_pos"]."</td>";
                                echo "<td><a href='#' data-toggle='tooltip' title='".$result["how"]."'><img src=\"data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMS4xLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDQ3NSA0NzUiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDQ3NSA0NzU7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iMTZweCIgaGVpZ2h0PSIxNnB4Ij4KPGc+Cgk8Zz4KCQk8Zz4KCQkJPHBhdGggZD0iTTIzOCwwQzEwNywwLDAsMTA3LDAsMjM4czEwNywyMzcsMjM4LDIzN3MyMzctMTA2LDIzNy0yMzdTMzY5LDAsMjM4LDB6IE0yMzgsNDMyICAgICBjLTEwNywwLTE5NC04Ny0xOTQtMTk0UzEzMSw0NCwyMzgsNDRzMTk0LDg3LDE5NCwxOTRTMzQ1LDQzMiwyMzgsNDMyeiIgZmlsbD0iI0Q4MDAyNyIvPgoJCQk8cGF0aCBkPSJNMjM2LDg5Yy04MCwwLTk4LDU5LTk4LDgyYzAsMTIsMTAsMjEsMjIsMjFzMjItOSwyMi0yMWMwLTksNS0zOSw1NC0zOWMyNCwwLDQyLDcsNTMsMTkgICAgIGMxNCwxNSwxMywzNSwxMiw0M2MtMywyMy0xNSwzNS0zOCwzNWMtNDAsMC00OSwyMi00OSw0MHYxOGMwLDEyLDEwLDIyLDIyLDIyczIyLTEwLDIyLTIydi0xNGMxLDAsMy0xLDUtMWMzNSwwLDc1LTE5LDgxLTczICAgICBjMy0yOS00LTU3LTIyLTc3QzMwOSwxMDcsMjgyLDg5LDIzNiw4OXoiIGZpbGw9IiNEODAwMjciLz4KCQkJPHBhdGggZD0iTTIzNiwzMzFjLTE1LDAtMjcsMTItMjcsMjdzMTIsMjcsMjcsMjdzMjctMTIsMjctMjdTMjUxLDMzMSwyMzYsMzMxeiIgZmlsbD0iI0Q4MDAyNyIvPgoJCTwvZz4KCTwvZz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K\" /></a></td>";
                                echo "<td><a href='#' data-html='true' data-toggle='tooltip' title='". (isset($result["rules"]) ? $result["rules"] : "Brak") ."'><img src=\"data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMS4xLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDQ3NSA0NzUiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDQ3NSA0NzU7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iMTZweCIgaGVpZ2h0PSIxNnB4Ij4KPGc+Cgk8Zz4KCQk8Zz4KCQkJPHBhdGggZD0iTTIzOCwwQzEwNywwLDAsMTA3LDAsMjM4czEwNywyMzcsMjM4LDIzN3MyMzctMTA2LDIzNy0yMzdTMzY5LDAsMjM4LDB6IE0yMzgsNDMyICAgICBjLTEwNywwLTE5NC04Ny0xOTQtMTk0UzEzMSw0NCwyMzgsNDRzMTk0LDg3LDE5NCwxOTRTMzQ1LDQzMiwyMzgsNDMyeiIgZmlsbD0iI0Q4MDAyNyIvPgoJCQk8cGF0aCBkPSJNMjM2LDg5Yy04MCwwLTk4LDU5LTk4LDgyYzAsMTIsMTAsMjEsMjIsMjFzMjItOSwyMi0yMWMwLTksNS0zOSw1NC0zOWMyNCwwLDQyLDcsNTMsMTkgICAgIGMxNCwxNSwxMywzNSwxMiw0M2MtMywyMy0xNSwzNS0zOCwzNWMtNDAsMC00OSwyMi00OSw0MHYxOGMwLDEyLDEwLDIyLDIyLDIyczIyLTEwLDIyLTIydi0xNGMxLDAsMy0xLDUtMWMzNSwwLDc1LTE5LDgxLTczICAgICBjMy0yOS00LTU3LTIyLTc3QzMwOSwxMDcsMjgyLDg5LDIzNiw4OXoiIGZpbGw9IiNEODAwMjciLz4KCQkJPHBhdGggZD0iTTIzNiwzMzFjLTE1LDAtMjcsMTItMjcsMjdzMTIsMjcsMjcsMjdzMjctMTIsMjctMjdTMjUxLDMzMSwyMzYsMzMxeiIgZmlsbD0iI0Q4MDAyNyIvPgoJCTwvZz4KCTwvZz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K\" /></a></td>";
                                echo "<td><a href='#' data-toggle='tooltip' title='".rtrim($result["facts"],", ")."'><img src=\"data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMS4xLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDQ3NSA0NzUiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDQ3NSA0NzU7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iMTZweCIgaGVpZ2h0PSIxNnB4Ij4KPGc+Cgk8Zz4KCQk8Zz4KCQkJPHBhdGggZD0iTTIzOCwwQzEwNywwLDAsMTA3LDAsMjM4czEwNywyMzcsMjM4LDIzN3MyMzctMTA2LDIzNy0yMzdTMzY5LDAsMjM4LDB6IE0yMzgsNDMyICAgICBjLTEwNywwLTE5NC04Ny0xOTQtMTk0UzEzMSw0NCwyMzgsNDRzMTk0LDg3LDE5NCwxOTRTMzQ1LDQzMiwyMzgsNDMyeiIgZmlsbD0iI0Q4MDAyNyIvPgoJCQk8cGF0aCBkPSJNMjM2LDg5Yy04MCwwLTk4LDU5LTk4LDgyYzAsMTIsMTAsMjEsMjIsMjFzMjItOSwyMi0yMWMwLTksNS0zOSw1NC0zOWMyNCwwLDQyLDcsNTMsMTkgICAgIGMxNCwxNSwxMywzNSwxMiw0M2MtMywyMy0xNSwzNS0zOCwzNWMtNDAsMC00OSwyMi00OSw0MHYxOGMwLDEyLDEwLDIyLDIyLDIyczIyLTEwLDIyLTIydi0xNGMxLDAsMy0xLDUtMWMzNSwwLDc1LTE5LDgxLTczICAgICBjMy0yOS00LTU3LTIyLTc3QzMwOSwxMDcsMjgyLDg5LDIzNiw4OXoiIGZpbGw9IiNEODAwMjciLz4KCQkJPHBhdGggZD0iTTIzNiwzMzFjLTE1LDAtMjcsMTItMjcsMjdzMTIsMjcsMjcsMjdzMjctMTIsMjctMjdTMjUxLDMzMSwyMzYsMzMxeiIgZmlsbD0iI0Q4MDAyNyIvPgoJCTwvZz4KCTwvZz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K\" /></a></td>";
                                echo "</tr>";
                            }
                        }
                        catch(\Exception $e)
                        {
                            \Utils\Front::error($e->getMessage());
                        }
                    }

                    ?>
                </table>
            </div>
        </div>
    </div>
    <!--<div class="col-md-1"></div>
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-database"> Fakty</i>
            </div>
            <div class="box-body">
                <table class="table table-bordered" style="text-align: center;">
                    <tr>
                        <td>No.</td>
                        <td>Imię i nazwisko</td>
                        <td>Fakty</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>-->
</div>
<script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>