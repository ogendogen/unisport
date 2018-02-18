<?php

$CONF = array();
$CONF["site"] = "http://unisport.az.pl";
$CONF["debug"] = false;
$CONF["db"] = array();
$CONF["db"]["host"] = "localhost";
$CONF["db"]["user"] = "cskatowi_sport";
$CONF["db"]["pass"] = "0OOn1NkP";
$CONF["db"]["db"] = "cskatowi_sport";
$CONF["sitekey"] = "6Lcg_UYUAAAAAEwEJnx7VBl6atcTLillwJLWt-e-";
$CONF["privatekey"] = "6Lcg_UYUAAAAADZIK6EBU52nyGSyeO0I7QPLFXRf";

if ($CONF["debug"])
{
    error_reporting(-1);
    ini_set("display_errors", 1);
}

?>
