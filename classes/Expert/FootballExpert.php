<?php

namespace
{
    require_once("Expert.php");
}

namespace Expert
{
    class FootballExpert extends Expert
    {
        private $data;
        private $analyse_rettmp;
        public function __construct(int $team_id)
        {
            try
            {
                parent::__construct($team_id);
                parent::checkRequirements();
                $this->data = parent::getAllTeamPlayersActions();
                echo var_dump($this->data);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function doAnalyse(int $goalkeeper_id, array $players_to_omit = null) : array
        {
            try
            {
                //if (!$this->team->isUserInTeam($goalkeeper_id)) throw new \Exception("Proponowany bramkarz nie jest w drużynie!");
                $ret = array(); // returning array with results
                //$ret["playerid"]
                //$ret["playerpos"]

                $this->analyse_rettmp = array();
                $toanalyse = $this->getPlayersToAnalyse(); // players left to analyse
                foreach($toanalyse as $playerid)
                {
                    $row = array();
                    $row["playerid"] = $playerid;
                    if ($playerid == $goalkeeper_id)
                    {
                        $row["playerpos"] = "Bramkarz";
                        array_push($ret, $row);
                        continue;
                    }

                    if ($this->isTheMostGoals($playerid))
                    {
                        if ($this->isTheMostAccurateShots($playerid) && \Utils\General::counInNestedArrayByKey($toanalyse, "playerpos", "Środkowy napastnik"))
                        {
                            $row["playerpos"] = "Środkowy napastnik";
                            array_push($ret, $row);
                            continue;
                        }
                    }

                    if ($this->isTheMostAssists($playerid) && \Utils\General::counInNestedArrayByKey($toanalyse, "playerpos", "Środkowy pomocnik"))
                    {
                        $row["playerpos"] = "Środkowy pomocnik";
                        array_push($ret, $row);
                        continue;
                    }
                    else
                    {
                        if ($this->isTheMostShots($playerid) && \Utils\General::counInNestedArrayByKey($toanalyse, "playerpos", "Skrzydłowy napastnik"))
                        {
                            $row["playerpos"] = "Skrzydłowy napastnik";
                            array_push($ret, $row);
                            continue;
                        }
                        else
                        {
                            if ($this->isTheMostFauls($playerid))
                            {
                                if ($this->isTheMostOvertakes($playerid) && \Utils\General::counInNestedArrayByKey($toanalyse, "playerpos", "Skrzydłowy obrońca"))
                                {
                                    $row["playerpos"] = "Skrzydłowy obrońca";
                                    array_push($ret, $row);
                                    continue;
                                }
                            }

                            if ($this->isTheMostOffsets($playerid) && \Utils\General::counInNestedArrayByKey($toanalyse, "playerpos", "Skrzydłowy obrońca"))
                            {
                                $row["playerpos"] = "Skrzydłowy obrońca";
                                array_push($ret, $row);
                                continue;
                            }
                            else
                            {
                                $row["playerpos"] = "Skrzydłowy pomocnik";
                                array_push($ret, $row);
                                continue;
                            }
                        }
                    }
                }
                return $ret;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function getPlayersToAnalyse() : array
        {
            try
            {
                $ret = array();
                foreach ($this->data as $player) array_push($ret, $player["user_id"]);
                return $ret;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function isTheMostGoals(int $userid) : bool
        {
            try
            {
                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function isTheMostAssists(int $userid) : bool
        {
            try
            {
                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function isTheMostShots(int $userid) : bool
        {
            try
            {
                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function isTheMostFauls(int $userid) : bool
        {
            try
            {
                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function isTheMostOvertakes(int $userid) : bool
        {
            try
            {
                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function isTheMostOffsets(int $userid) : bool
        {
            try
            {
                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function isTheMostAccurateShots(int $userid) : bool
        {
            try
            {
                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}

namespace
{
    try
    {
        $expert = new \Expert\FootballExpert(4);
        $expert->doAnalyse(3);
    }
    catch (\Exception $e)
    {
        echo $e->getMessage();
    }
}