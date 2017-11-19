<?php

namespace {
    require_once(__DIR__."/Front.php");
    require_once(__DIR__."/../../configs/config.php");
}

namespace Utils
{
    use \TCPDF;
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

                function fun() {
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

        public static function retJsonArray(int $code, array $msg, int $arg = 256) : string
        {
            $ret = array();
            $ret["code"] = $code;
            $ret["arr"] = $msg;
            return json_encode($ret, $arg);
        }

        public static function in_array_r($needle, $haystack, $strict = false)
        {
            foreach ($haystack as $item) {
                if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::in_array_r($needle, $item, $strict))) {
                    return true;
                }
            }
            return false;
        }

        public static function validateDate(string $date, string $format)
        {
            $d = \DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) === $date;
        }

        public static function preparePDF(int $game_id = 0) : TCPDF
        {
            require_once(__DIR__."/TCPDF/tcpdf.php");
            // create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('UniSport');
            $pdf->SetTitle('Podsumowanie meczu');
            $pdf->SetKeywords('Unisport, sport, mecz');

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, "Podsumowanie meczu ".($game_id != 0 ? "nr ".$game_id : ""), "UniSport ".date("d-m-Y H:m", time()), array(0,64,255), array(0,64,128));
            $pdf->setFooterData(array(0,64,0), array(0,64,128));

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // set default font subsetting mode
            $pdf->setFontSubsetting(true);

            return $pdf;
        }
    }
}