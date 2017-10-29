<?php

\Utils\Front::printPageDesc("Mecze", "Zarządzanie meczami");


if (!isset($_GET["teamChoosen"])) include(__DIR__."/../minor/Games-TeamNotChoosen.php");
else include(__DIR__."/../minor/Games-TeamChoosen.php");

?>