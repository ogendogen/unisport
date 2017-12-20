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
        private $analyse_buffer;
        public function __construct(int $team_id)
        {
            try
            {
                parent::__construct($team_id);
                $this->data = parent::getAllTeamPlayersActions();
                //echo var_dump($this->data);
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
                if (!$this->team->isUserInTeam($goalkeeper_id)) throw new \Exception("Proponowany bramkarz nie jest w drużynie!");
                if (!is_null($players_to_omit) && count($this->team->getAllTeamPlayers()) - count($players_to_omit) < 11) throw new \Exception("Drużyna musi składać się z 11 graczy!");
                $ret = array(); // returning array with results
                $this->analyse_buffer = array();
                //$ret["playerid"]
                //$ret["playerpos"]

                $this->analyse_rettmp = array();
                $toanalyse = $this->getPlayersToAnalyse(); // players left to analyse
                foreach($toanalyse as $playerid)
                {
                    if (!is_null($players_to_omit) && in_array($playerid, $players_to_omit)) continue;
                    $row = array();
                    $row["playerid"] = $playerid;
                    if ($playerid == $goalkeeper_id)
                    {
                        $row["playerpos"] = "Bramkarz";
                        array_push($ret, $row);
                        continue;
                    }

                    if ($this->isTheMostActions($playerid, "goal") && \Utils\General::countInNestedArrayByKey($row, "playerpos", "goal") == 1)
                    {
                        if ($this->isTheMostActions($playerid, "accurate_shot"))
                        {
                            $row["playerpos"] = "Środkowy napastnik";
                            array_push($ret, $row);
                            array_push($this->analyse_buffer, "Środkowy napastnik");
                            continue;
                        }
                    }

                    if ($this->isTheMostActions($playerid, "assist") && \Utils\General::countInNestedArrayByKey($row, "playerpos", "assist") == 1)
                    {
                        $row["playerpos"] = "Środkowy pomocnik";
                        array_push($ret, $row);
                        array_push($this->analyse_buffer, "Środkowy pomocnik");
                        continue;
                    }
                    else
                    {
                        if ($this->isTheMostActions($playerid, "shot") && \Utils\General::countInNestedArrayByKey($row, "playerpos", "shot") == 2)
                        {
                            $row["playerpos"] = "Skrzydłowy napastnik";
                            array_push($ret, $row);
                            array_push($this->analyse_buffer, "Skrzydłowy napastnik");
                            continue;
                        }
                        else
                        {
                            if ($this->isTheMostActions($playerid, "faul"))
                            {
                                if ($this->isTheMostActions($playerid, "overtake") && \Utils\General::countInNestedArrayByKey($row, "playerpos", "overtake") == 2)
                                {
                                    $row["playerpos"] = "Skrzydłowy obrońca";
                                    array_push($ret, $row);
                                    array_push($this->analyse_buffer, "Skrzydłowy obrońca");
                                    continue;
                                }
                            }

                            if ($this->isTheMostActions($playerid, "offset") && \Utils\General::countInNestedArrayByKey($row, "playerpos", "offset") == 2)
                            {
                                $row["playerpos"] = "Skrzydłowy obrońca";
                                array_push($ret, $row);
                                array_push($this->analyse_buffer, "Skrzydłowy obrońca");
                                continue;
                            }
                            else
                            {
                                $row["playerpos"] = "Skrzydłowy pomocnik";
                                array_push($ret, $row);
                                array_push($this->analyse_buffer, "Skrzydłowy pomocnik");
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
                return array_unique($ret);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function isTheMostActions(int $userid, string $action) : bool // check if action is valid (equals enum in db)
        {
            try
            {
                $collection = array();
                foreach ($this->data as $player)
                {
                    if ($player["football_action"] == $action)
                    {
                        if (isset($collection[strval($player["user_id"])])) $collection[strval($player["user_id"])]++;
                        else $collection[strval($player["user_id"])] = 1;
                    }
                }

                echo "id = ".$userid." ";
                var_dump($collection);
                echo " ".$action;
                echo "<br>";
                if (empty($collection)) return false;

                $max = array_keys($collection, max($collection));
                return $max[0] == $userid;
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
        //var_dump($expert->isTheMostActions(5, "goal"));
        $ret = $expert->doAnalyse(12);
        foreach ($ret as $player)
        {
            $user = new \User\LoggedUser($player["playerid"]);
            echo "<span style='font-weight: bold;'>".$user->getUserCredentials()."</span> został wytypowany na pozycję <span style='font-weight: bold;'>".\Utils\Dictionary::keyToWord($player["playerpos"])."</span><br>";
        }
    }
    catch (\Exception $e)
    {
        echo $e->getMessage();
    }
}