<?php

\Utils\Front::printPageDesc("Kalendarz", "Kalendarz z wydarzeniami drużynowymi");

if (!isset($_GET["teamid"])) include(__DIR__."/../minor/Calendar-TeamNotChoosen.php");
else include(__DIR__."/../minor/Calendar-TeamChoosen.php");

?>