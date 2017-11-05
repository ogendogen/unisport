<?php

session_start();
unset($_SESSION["userid"]);
session_destroy();

require_once(__DIR__."/../classes/Utils/General.php");
header('Content-Type: application/json'); // json response
die(\Utils\General::retJson(1, "Wylogowany poprawnie!"));

?>