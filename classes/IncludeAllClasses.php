<?php

require_once(__DIR__."/../configs/config.php");
global $CONF;
$dirs = scandir(__DIR__);
foreach ($dirs as $dir)
{
    if ($dir == "." || $dir == ".." || $dir == "IncludeAllClasses.php") continue;
    $subdirs = scandir(__DIR__."/".$dir."/");
    foreach ($subdirs as $subdir)
    {
        if ($subdir == "." || $subdir == ".." || $subdir == "TCPDF") continue;
        require_once(__DIR__."/".$dir."/".$subdir);
    }
}

?>