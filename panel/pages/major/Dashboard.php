<?php

\Utils\Front::printPageDesc("Pulpit", "Główna strona aplikacji");
$events = \Team\Calendar::getAllTeamsIncomingEvents($_SESSION["userid"]);

if (isset($_POST["notepad"]))
{
    try
    {
        \Utils\Validations::validateInput($_POST["notepad"]);
        global $logged_user;
        $logged_user->setUserNotepad($_POST["notepad"]);
    }
    catch(\Exception $e)
    {
        \Utils\Front::error($e->getMessage());
    }
}

?>

<div class="row">
    <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4><i class="fa fa-pencil-square-o"></i> Szybkie notatki</h4>
            </div>
            <div class="box-body" style="height: 400px;">
                <form method="post" style="height: 350px;">
                    <textarea style="width: 100%; height: 100%;" name="notepad" title="notepad"><?php global $logged_user; echo trim($logged_user->getUserNotepad());?></textarea>
                    <input type="submit" class="btn btn-primary btn-block" value="Zapisz">
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-5">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4><i class="fa fa-calendar"></i> Zbliżające się wydarzenia</h4>
            </div>
            <div class="box-body">
                <?php

                if (empty($events)) echo "<div class='alert alert-danger btn-block'>Nie masz zbliżających się wydarzeń w najbliższych dniach</div>";
                else
                {
                    foreach ($events as $event)
                    {
                        try
                        {
                            $tmp = new \Team\Team($event["calendar_teamid"]);
                            echo "<p><div class='alert alert-info btn-block'>Dnia ".$event["calendar_startdate"]." zaplanowane zostało ". ($event["calendar_priority"] == "high" ? "<strong>ważne</strong>" : "") ."wydarzenie <span style='font-weight: bold;'>".$event["calendar_event"]."</span> w drużynie <span style='font-weight: bold'>".$tmp->getTeamInfo()["team_name"]."</span></div></p>";
                        }
                        catch (\Exception $e)
                        {
                            \Utils\Front::error($e->getMessage());
                        }

                    }
                }

                ?>
            </div>
        </div>
    </div>
    <div class="col-md-2"></div>
</div>