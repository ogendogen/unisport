<?php

session_start();

require_once("../configs/config.php");
require_once("../classes/IncludeAllClasses.php");

include("pages/parts/Header.php");

if (!isset($_SESSION["userid"]))
{
    global $CONF;
    include("pages/parts/Modal.html");
    \Utils\General::redirectWithMessageAndDelay($CONF["site"]."?tab=login", "Uwaga", "Nie jesteś zalogowany", "warning", 3);
    die;
}

$logged_user = null;
try
{
    $logged_user = new \User\LoggedUser($_SESSION["userid"]);
    if (!$logged_user->isUserActivated())
    {
        global $CONF;
        include("pages/parts/Modal.html");
        \Utils\General::redirectWithMessageAndDelay($CONF["site"], "Uwaga", "Musisz najpierw potwierdzić adres email!", "warning", 3);
        die;
    }
}
catch (\Exception $e)
{
    die("Wystąpił błąd: ". $e->getMessage());
}

include("pages/parts/Navbar.php");
include("pages/parts/Modal.html");
include("pages/parts/LeftNavbar.php");
?>

<?php

if (isset($_GET["tab"]))
{
    switch($_GET["tab"])
    {
        case "home":
            include("pages/major/Dashboard.php");
            break;

        case "teams":
            include("pages/major/Teams.php");
            break;

        case "games":
            include("pages/major/Games.php");
            break;

        case "teamedit":
            include("pages/major/TeamEdit.php");
            break;

        case "membersedit":
            include("pages/major/MembersEdit.php");
            break;

        case "definesport":
            include("pages/major/DefineSport.php");
            break;

        case "definesport_edit":
            include("pages/major/DefineSportEdit.php");
            break;

        case "teamcalendar":
            include("pages/major/Calendar.php");
            break;

        case "mailbox":
            include("pages/major/MailBox.php");
            break;

        case "medical":
            include("pages/major/Medical.php");
            break;

        default:
            include("pages/major/Dashboard.php");
            break;
    }
}
else
{
    include("pages/major/Dashboard.php");
}

?>

<?php

include("pages/parts/Footer.php");
include("pages/parts/RightNavbar.php");
include("pages/parts/GlobalScript.php");
include("pages/parts/Closing.php");

?>