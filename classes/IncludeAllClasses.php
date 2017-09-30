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

/*$root_path = "/home/cskatowi/domains/unisport.cskatowice.com/public_html/";

$items = scandir($root_path."classes"); // get list of all directories and files
$dirs = array();
$files = array();

foreach ($items as $item)
{
    if (is_dir($item) && $item != "." && $item != "..") // scan all dirs
    {
        $subitems = scandir($item);
        foreach ($subitems as $subitem) // include all files in subdirectory
        {
            if (strpos($subitem, ".php") !== false)
            {
                if ($subitem == "IncludeAllClasses.php") continue;
                echo "./".$item."/".$subitem."<br>";
                //include("./".$item."/".$subitem);
            }
        }
    }
}*/

//include("./Db/DbGeneral.php");
//$test = new Db\DbGeneral($CONF["db"]["host"], $CONF["db"]["user"], $CONF["db"]["pass"], $CONF["db"]["db"]);
//var_dump($test);

?>