<?php

namespace {
    require_once(__DIR__."/Front.php");
    require_once(__DIR__."/../../configs/config.php");
}

namespace Utils
{
    class General
    {
        public static function redirect(string $url)
        {
            header("Location: " . $url);
        }

        public static function redirectWithMessageAndDelay(string $url, string $msgheader, string $msg, string $type, int $delay)
        {
            ?>

            <script>
                setTimeout(fun, <?php echo $delay * 1000; ?>);

                function fun()
                {
                    window.location.href = "<?php echo $url; ?>";
                }
            </script>

            <?php
            \Utils\Front::modal($msgheader, $msg, $type, false);
        }

        public static function getIP(): string
        {
            return $_SERVER['REMOTE_ADDR'];
        }

        public static function getRandomString(int $len = 16): string
        {
            return substr(md5(rand()), 0, $len);
        }

        public static function isStartsWithUpper(string $str): bool
        {
            $chr = mb_substr($str, 0, 1, "UTF-8");
            return mb_strtolower($chr, "UTF-8") != $chr;
        }

        public static function sendConfirmationMail(string $to, int $id, string $confirmation_code)
        {
            $title = "Potwierdzenie email UniSport";
            $msg = "Dziękujemy za rejestracje w systemie UniSport<br>Aby dokończyć rejestrację kliknij w link ";

            global $CONF;
            $msg .= $CONF["site"] . "/other/confirmation.php?id=" . $id . "&code=" . $confirmation_code . " <br>";
            $msg .= "Jeżeli to pomyłka, zignoruj wiadomość";

            $ret = mail($to, $title, $msg);
            if (!$ret) throw new \Exception("Problem z wysłaniem wiadomości email");
        }

        public static function retJson(int $code, string $msg, int $arg = 256) : string
        {
            $ret = array();
            $ret["msg"] = $msg;
            $ret["code"] = $code;
            return json_encode($ret, $arg);
        }
    }
}