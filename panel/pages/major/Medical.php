<?php

\Utils\Front::printPageDesc("Moduł medyczny", "Stan zdrowia");

if (isset($_GET["teamid"]) && isset($_GET["playerid"])) include(__DIR__."/../minor/Medical-TeamChoosen.php");
else include(__DIR__."/../minor/Medical-TeamNotChoosen.php");

?>