<?php

\Utils\Front::printPageDesc("Mecze", "Zarządzanie meczami");


if (!isset($_GET["teamid"])) include(__DIR__."/../minor/Games-TeamNotChoosen.php");
else
{
    if (isset($_GET["minor"]))
    {
        switch($_GET["minor"])
        {
            case "addgame":
                include(__DIR__."/../minor/Games-AddGame.php");
                break;

            default:
                include(__DIR__."/../minor/Games-TeamChoosen.php");
                break;
        }
    }
    else
    {
        include(__DIR__."/../minor/Games-TeamChoosen.php");
    }
}

?>