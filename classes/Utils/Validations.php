<?php

namespace {
    require_once(__DIR__."/../../configs/config.php");
    require_once(__DIR__."/General.php");
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
            $ip = \Utils\General::getIP();
            $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$CONF["privatekey"]."&response=".$captcha."&remoteip=".$ip);
            $responseKeys = json_decode($response, true);
            if (intval($responseKeys["success"]) !== 1) throw new \Exception("Captcha niepoprawna. Spróbuj ponownie");
        }

        public static function validatePostArray(array $array)
        {
            foreach ($array as $key => $value) {
                if ($value == "0") continue;
                if (empty($value)) throw new \Exception("Uzupełnij wszystkie pola!");
            }
        }

        public static function isDateValid(string $date, string $format = 'Y-m-d H:i:s') : bool
        {
            $d = \DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }

        public static function validateDate(string $date, string $format)
        {
            $d = \DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) === $date;
        }

        public static function validateInput(string $input) : string
        {
            return htmlspecialchars(trim(stripslashes($input)));
        }

        public static function validateWholeArray(array &$arr)
        {
            foreach($arr as $key => $value)
            {
                if (is_string($value))
                {
                    $arr[$key] = self::validateInput($value);
                }
                else if (is_array($value))
                {
                    foreach ($value as $subkey => $subvalue)
                    {
                        $arr[$key[$subkey]] = self::validateInput($subvalue);
                    }
                }
            }
        }
    }
}