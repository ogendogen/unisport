<?php

if (isset($_GET["minor"]))
{
    switch($_GET["minor"])
    {
        case "main":
            include(__DIR__."/../minor/MailBox-Main.php");
            break;

        case "send":
            include(__DIR__."/../minor/MailBox-Send.php");
            break;

        case "sent":
            include(__DIR__."/../minor/MailBox-Sent.php");
            break;

        case "message":
            include(__DIR__."/../minor/MailBox-Message.php");
            break;
    }
}
else
{
    include(__DIR__."/../minor/MailBox-Main.php");
}

?>