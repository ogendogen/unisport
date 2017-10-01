<?php

session_start();
if (!isset($_SESSION["userid"])) die("Musisz być zalogowany !");
else echo "Jesteś zalogowany jako user o id ".$_SESSION["userid"];

?>