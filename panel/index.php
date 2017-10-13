<?php

session_start();

if (!isset($_SESSION["userid"])) die("Musisz być zalogowany !");

require_once("../configs/config.php");
require_once("../classes/IncludeAllClasses.php");

$logged_user = null;
try
{
    $logged_user = new \User\LoggedUser($_SESSION["userid"]);
    if (!$logged_user->isUserActivated()) die("Musisz najpierw potwierdzić adres email!");
}
catch (\Exception $e)
{
    die("Wystąpił błąd: ". $e->getMessage());
}

include("pages/parts/Header.php");
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

        case "matches":
            include("pages/major/Matches.php");
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

</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php

include("pages/parts/Footer.php");
include("pages/parts/RightNavbar.php");
include("pages/parts/GlobalScript.php");
include("pages/parts/Closing.php");

?>