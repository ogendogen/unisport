<?php

require_once(__DIR__."/../configs/config.php");
global $CONF;
$dirs = scandir($CONF["root_path"]."classes/");
foreach ($dirs as $dir)
{
    if ($dir == "." || $dir == ".." || $dir == "IncludeAllClasses.php") continue;
    $subdirs = scandir($CONF["root_path"]."classes/".$dir."/");
    foreach ($subdirs as $subdir)
    {
        if ($subdir == "." || $subdir == "..") continue;
        require_once($CONF["root_path"]."classes/".$dir."/".$subdir);
    }
}

?>