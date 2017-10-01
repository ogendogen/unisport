<?php

namespace {
    require_once(__DIR__."/../../configs/config.php");
}

namespace Utils
{
    class Validations
    {
        public static function isEmail(string $input) : bool
        {
            $email_pattern = "/.+@.+\.\w{2,3}/";
            if (preg_match($email_pattern, $input) === 1) return true;
            return false;
        }

        public static function verifyResponse(string $captcha)
        {
            if (!$captcha) throw new \Exception("Captcha niepoprawna. Spróbuj ponownie");
            global $CONF;
            $ip = $_SERVER["REMOTE_ADDR"];
            $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$CONF["privatekey"]."&response=".$captcha."&remoteip=".$ip);
            $responseKeys = json_decode($response, true);
            if (intval($responseKeys["success"]) !== 1) throw new \Exception("Captcha niepoprawna. Spróbuj ponownie");
        }
    }
}