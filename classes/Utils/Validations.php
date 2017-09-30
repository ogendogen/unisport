<?php

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
    }
}