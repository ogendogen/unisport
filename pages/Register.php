<?php
if (isset($_SESSION["userid"]) && is_numeric($_SESSION["userid"])) // user zalogowany
{
    \Utils\Front::modal("Uwaga!", "Już masz utworzone konto!", "warning");
}
else if (isset($_POST["login"])) // formularz wysłany
{
    try
    {
        $user = new \User\UserGeneral();
        \Utils\General::validatePostArray($_POST);
        $login = htmlspecialchars(trim($_POST["login"]));
        $pass = htmlspecialchars(trim($_POST["pass"]));
        $pass2 = htmlspecialchars(trim($_POST["pass2"]));
        $email = htmlspecialchars(trim($_POST["email"]));
        $name = htmlspecialchars(trim($_POST["name"]));
        $surname = $user->normalizeSurname(htmlspecialchars(trim($_POST["surname"])));
        $user->validateNewUser($login, $pass, $pass2, $email, $name, $surname);
        $arr = $user->registerUser($login, $pass, $email, $name, $surname); // todo: ustalić w jakiej klasie będzie ta metoda + ustalenie abstrakcyjności
        \Utils\General::sendConfirmationMail($email, $arr["id"], $arr["code"]);
        \Utils\Front::success("Użytkownik zarejestrowany poprawnie! Potwierdź swój adres email.");
    }
    catch (\Exception $e)
    {
        \Utils\Front::warning($e->getMessage());
    }
}

?>

<div class="container">
	<div class="row" style="margin-top: 50px;">

		<div class="col-md-12">
			<form role="form" method="post" action="?tab=register">

				<legend class="text-center">Rejestracja</legend>

				<fieldset class="text-center">

					<div class="form-group col-xs-12 col-md-6 col-md-offset-3">
						<label for="first_name">Login</label>
						<input type="text" class="form-control" name="login" id="first_name" placeholder="Twój login">
					</div>

					<div class="form-group col-xs-12 col-md-6 col-md-offset-3">
						<label for="password">Hasło</label>
						<input type="password" class="form-control" name="pass" id="password" placeholder="Twoje hasło" onblur="validatePasswords()">
					</div>

					<div class="form-group col-xs-12 col-md-6 col-md-offset-3">
						<label for="password">Potwierdź hasło</label>
						<input type="password" class="form-control" name="pass2" id="password2" placeholder="Potwierdź hasło" onblur="validatePasswords()">
					</div>

					<div class="form-group col-xs-12 col-md-6 col-md-offset-3">
						<label for="password">Email</label>
						<input type="text" class="form-control" name="email" id="email" placeholder="Twój email" onblur="validateEmail()">
					</div>

                    <div class="form-group col-xs-12 col-md-6 col-md-offset-3">
                        <label for="password">Imię</label>
                        <input type="text" class="form-control" name="name" placeholder="Twoje imię">
                    </div>

                    <div class="form-group col-xs-12 col-md-6 col-md-offset-3">
                        <label for="password">Nazwisko</label>
                        <input type="text" class="form-control" name="surname" id="surname" placeholder="Twoje nazwisko">
                    </div>

				</fieldset>

				<div class="form-group text-center">
					<div class="col-md-12">
						<button type="submit" id="btn" class="btn btn-primary">Zarejestruj się</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>