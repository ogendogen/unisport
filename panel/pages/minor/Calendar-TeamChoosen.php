<?php

$team = null;
$calendar = null;
try
{
    if (!isset($_GET["teamid"])) throw new \Exception("Nie wybrałeś drużyny!", 21);
    else $team = new \Team\Team($_GET["teamid"]);

    $events = null;
    $calendar = new \Team\Calendar($_GET["teamid"]);
    if (isset($_POST["eventname"]))
    {
        \Utils\Validations::validatePostArray($_POST);
        \Utils\Validations::validateWholeArray($_POST);

        $eventname = $_POST["eventname"];
        $eventpriority = $_POST["eventpriority"];
        $eventstarttime = $_POST["eventstartdatetime"];
        $eventendtime = $_POST["eventenddatetime"];

        $calendar->addEvent($eventstarttime, $eventendtime, $eventname, $eventpriority);
        \Utils\Front::success("Wydarzenie dodane poprawnie do kalendarza");
    }
}
catch (\Exception $e)
{
    if ($e->getCode() == 21) \Utils\General::redirectWithMessageAndDelay("?tab=dashboard", "Uwaga", $e->getMessage(), "warning", 2);
    else throw $e;
}


?>
<div class="row">
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header">
                <h4>Zaplanuj nowe wydarzenie</h4>
            </div>
            <div class="box-body">
                <form method="post">
                    <p><label for="eventname">Krótki opis wydarzenia</label></p>
                    <p><input type="text" class="btn-block" name="eventname" title="eventname" placeholder="Trening wieczorem"></p>

                    <p><label for="eventpriority">Priorytet</label>
                        <select title="eventpriority" name="eventpriority">
                            <option value="low">Niski</option>
                            <option value="medium" selected="selected">Normalny</option>
                            <option value="high">Wysoki</option>
                        </select>
                    </p>

                    <p><label for="eventtime">Początek wydarzenia</label></p>
                    <div class="form-group">
                        <div class='input-group date' id='datetimepicker1'>
                            <input id="matchdatetime" title="calendardatetime" name="eventstartdatetime" type='text' class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>

                    <p><label for="eventtime">Koniec wydarzenia</label></p>
                    <div class="form-group">
                        <div class='input-group date' id='datetimepicker2'>
                            <input id="matchdatetime" title="calendardatetime" name="eventenddatetime" type='text' class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>

                    <input type="submit" id="eventadd" class="btn btn-primary btn-block" value="Dodaj">
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-body no-padding">
                <div id="calendar" class="fc fc-unthemed fc-ltr">
                    <!-- Calendar goes here -->
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('#datetimepicker1').datetimepicker({
            locale: "pl",
            defaultDate: moment()
        });
        $('#datetimepicker2').datetimepicker({
            locale: "pl",
            defaultDate: moment().add(1, "hours")
        });

        <?php echo "var events = ".json_encode($calendar->getAllTeamEvents()); ?>

        var len = events.length;
        for (var i = 0; i < len; i++)
        {
            var ret = addEvent(events[i].calendar_id, events[i].calendar_event, events[i].calendar_startdate, events[i].calendar_enddate, events[i].calendar_priority);
            console.log(ret);
        }

        $('#calendar').fullCalendar('rerenderEvents');

        checkLeadership(getUrlParameter("teamid"));
        if (window.localStorage.isLeader !== "1") $("#eventadd").prop("disabled", "disabled");
    });
</script>
