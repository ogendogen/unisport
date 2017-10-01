<?php

require("../configs/config.php");
require("../classes/User/UserGeneral.php");

global $CONF;
if (!isset($_GET["id"]) || !isset($_GET["code"]) || ($_GET["code"] == "0")) header("Location: ". $CONF["site"]);

try
{
    $user = new \User\UserGeneral();
    if ($user->checkConfiguration($_GET["id"], $_GET["code"]))
    {
        $user->confirmUser($_GET["id"]);
        include("confirmation_passed.html");
    }
    else
    {
        include("confirmation_failed.html");
    }
}
catch (\Exception $e)
{
    include("confirmation_failed.html");
}

?>