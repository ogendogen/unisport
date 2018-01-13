<?php

namespace
{
    require_once(__DIR__."/../Db/Database.php");
    require_once("LoggedUser.php");
    require_once(__DIR__."/../Team/Team.php");
}

namespace User
{
    class Medical
    {
        private $db;
        private $user;
        private $team;
        private $states = ["ok", "injured", "bad", "very fit"];

        public function __construct(int $user_id, int $team_id)
        {
            try
            {
                $this->db = \Db\Database::getInstance();

                $this->user = new \User\LoggedUser($user_id);
                $this->team = new \Team\Team($team_id);

                if (!$this->team->isUserInTeam($user_id)) throw new \Exception("Ten użytkownik nie jest w tej drużynie!");
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function addNewMedicalRecord(int $height, float $weight, float $waist, string $generalhealthstate, int $iscapable)
        {
            try
            {
                $weight = round($weight, 2);
                $waist = round($waist, 2);

                if ($height < 140 || $height > 250) throw new \Exception("Wysokość z poza zakresu! (140-250)");
                if ($weight < 30.00 || $weight > 200.00) throw new \Exception("Waga z poza zakresu! (30-200)");
                if ($waist < 30.00 || $waist > 120.00) throw new \Exception("Obwód pasa z poza zakresu! (30-120)");
                if (!in_array($generalhealthstate, $this->states)) throw new \Exception("Nieznany stan zdrowia");
                if ($iscapable != 0 && $iscapable != 1) throw new \Exception("Nieznany stan zdolności");

                $bmi = $this->countBMI($height, $weight);
                $fat = $this->countFat($weight, $waist);

                $this->db->exec("INSERT INTO `medical` SET 
                                            medical_userid = ?,
                                            medical_teamid = ?,
                                            medical_height = ?,
                                            medical_weight = ?,
                                            medical_fat = ?,
                                            medical_bmi = ?,
                                            medical_waist = ?,
                                            medical_generalhealthstate = ?,
                                            medical_iscapable = ?,
                                            medical_date = ?", [$this->user->getUserId(), $this->team->getTeamInfo()["team_id"], $height, $weight, $fat, $bmi, $waist, $generalhealthstate, $iscapable, time()]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getAllUserData() : array
        {
            try
            {
                return $this->db->exec("SELECT * FROM `medical`
                                          WHERE medical_userid = ? AND medical_teamid = ?
                                          ORDER BY medical_date DESC", [$this->user->getUserId(), $this->team->getTeamInfo()["team_id"]]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function deleteRow(int $id)
        {
            try
            {
                $this->db->exec("DELETE FROM `medical` WHERE medical_id = ?", [$id]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function countBMI(int $height, float $weight) : float
        {
            $f_height = floatval($height);
            $f_height = round($height / 100, 2);

            return round($weight / ($f_height * $f_height), 2);
        }

        private function countFat(float $weight, float $waist) : float
        {
            $a = 4.15 * $waist;
            $b = $a / 2.54;
            $c = 0.082 * $weight * 2.2;
            $d = $b - $c - 98.42;
            $e = $weight * 2.2;

            return round($d/$e * 100, 2);
        }
    }
}