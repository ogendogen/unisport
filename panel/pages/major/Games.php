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

            case "editgame":
                include(__DIR__."/../minor/Games-EditGame.php");
                break;

            case "showall":
                include(__DIR__."/../minor/Games-ShowAllGames.php");
                break;

            case "summary":
                include(__DIR__."/../minor/Games-Summary.php");
                break;

            case "expert":
                include(__DIR__."/../minor/Games-Expert.php");
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