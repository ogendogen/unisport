<?php

session_start();
require_once("configs/config.php");
include("classes/IncludeAllClasses.php");

include("pages/Header.php");
include("pages/MainNav.php");
include("pages/Modal.html");

// main body goes here ...
if (isset($_GET["tab"]))
{
    switch($_GET["tab"])
    {
        case "login":
            include("pages/Login.php");
            break;

        case "register":
            include("pages/Register.php");
            break;

        default:
            include("pages/Home.php");
            break;
    }
}
else
{
    include("pages/Home.php");
}

include("pages/Footer.php");
?>