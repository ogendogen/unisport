<?php

\Utils\Front::printPageDesc("Kalendarz", "Kalendarz z wydarzeniami drużynowymi");

?>

<div class="row">
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header">
                <h4>Zaplanuj nowe wydarzenie</h4>
            </div>
            <div class="box-body">
                <form method="post">
                    <p><label for="eventname">Krótki opis wydarzenia</label>
                    <input type="text" name="eventname" title="eventname"></p>

                    <p><label for="eventpriority">Priorytet</label>
                    <select title="eventpriority" name="eventpriority">
                        <option value="low">Niski</option>
                        <option value="medium" selected="selected">Normalny</option>
                        <option value="high">Wysoki</option>
                    </select></p>

                    <p><label for="eventtime">Czas wydarzenia</label>
                    <!-- Bootstrap Date Picker --></p>

                    <input type="submit" value="Dodaj">
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