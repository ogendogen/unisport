<?php

global $CONF;
if (isset($_SESSION["userid"]) && isset($_GET["logout"]) && $_GET["logout"] == 1) // wylogowanie się
{
    unset($_SESSION["userid"]);
    session_destroy();

    \Utils\General::redirectWithMessageAndDelay($CONF["site"], "Wylogowanie", "Zostałeś wylogowany poprawnie", "success", 2);
    die;
}
else if (isset($_POST["login"])) // formularz wysłany
{
    $login = htmlspecialchars(trim(stripslashes($_POST["login"])));
    $pass = htmlspecialchars(trim(stripslashes($_POST["pass"])));
    $is_email = (\Utils\Validations::isEmail($login) ? true : false);
    $captcha = $_POST["g-recaptcha-response"];

    try
    {
        \Utils\General::validatePostArray($_POST);
        //$user = new \User\UserGeneral();
        $user = new \User\User();
        $user->isPasswordCorrect($login, $pass, $is_email);
        \Utils\Validations::verifyResponse($captcha);
        $user->isUserActive($login);
        $_SESSION["userid"] = $user->getUserId($login); // todo: być może zbudować jakąś funkcję do tego zamiast wprost wpisywać dane do sesji
        $_SESSION["login"] = (\Utils\Validations::isEmail($login) ? $user->emailToLogin($login) : $login);
        \Utils\General::redirectWithMessageAndDelay($CONF["site"]."/panel", "Powodzenie", "Zalogowałeś się poprawnie! Za chwilę zostajesz przekierowany...", "success", 2);
    }
    catch (\Exception $e)
    {
        \Utils\Front::warning($e->getMessage());
    }
}
else if (isset($_SESSION["userid"])) // user zalogowany, ale trafił tutaj w inny sposób
{
    \Utils\General::redirect($CONF["site"]);
    die;
}

?>

<div class="container">
    <div id="login_row" class="row text-center">

        <div class="col-md-8 col-md-offset-2">
            <form role="form" method="post" action="?tab=login">

                <legend class="text-center">Logowanie się</legend>

                <fieldset>

                    <div class="form-group col-md-6">
                        <label for="first_name">Nick lub email</label>
                        <input type="text" class="form-control" name="login" id="first_name" placeholder="Twój nick/email">
                    </div>

                    <div class="form-group col-md-6">
                        <label for="password">Hasło</label>
                        <input type="password" class="form-control" name="pass" id="password" placeholder="Twoje hasło">
                    </div>

                    <div class="form-group col-xs-12 col-md-6 col-md-offset-3">
                        <div class="g-recaptcha" data-sitekey="6LeXwTIUAAAAAJ2ONKwfEMu8OdRBjnq69_Of5LXZ"></div>
                    </div>

                </fieldset>

                <div class="form-group">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            Zaloguj się
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
