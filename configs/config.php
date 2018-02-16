<?php

$CONF = array();
$CONF["site"] = "http://unisport.cskatowice.com";
$CONF["debug"] = true;
$CONF["db"] = array();
$CONF["db"]["host"] = "localhost";
$CONF["db"]["user"] = "cskatowi_sport";
$CONF["db"]["pass"] = "0OOn1NkP";
$CONF["db"]["db"] = "cskatowi_sport";
$CONF["sitekey"] = "6LeXwTIUAAAAAJ2ONKwfEMu8OdRBjnq69_Of5LXZ";
$CONF["privatekey"] = "6LeXwTIUAAAAAN0rhsEphrAC6b8TDpEysCyXQysQ";

if ($CONF["debug"])
{
    error_reporting(-1);
    ini_set("display_errors", 1);
}

?>