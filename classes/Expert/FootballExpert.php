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
        private $analyse_buffer; // used to collect already choosen players
        //private $positions = ["Bramkarz", "Środkowy napastnik", "Skrzydłowy napastnik", "Środkowy pomocnik", "Skrzydłowy pomocnik", "Środkowy obrońca", "Skrzydłowy obrońca"];
        private $positions = array();
        public function __construct(int $team_id)
        {
            try
            {
                parent::__construct($team_id);
                $this->data = parent::getAllTeamPlayersActions();

                $this->positions["Bramkarz"] = 1;
                $this->positions["Środkowy napastnik"] = 1;
                $this->positions["Skrzydłowy napastnik"] = 2;
                $this->positions["Środkowy pomocnik"] = 1;
                $this->positions["Skrzydłowy pomocnik"] = 2;
                $this->positions["Środkowy obrońca"] = 2;
                $this->positions["Skrzydłowy obrońca"] = 2;
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

                $counter = 0;
                foreach($toanalyse as $playerid)
                {
                    $counter++;
                    var_dump($toanalyse);
                    echo "<br>";

                    if (!is_null($players_to_omit) && in_array($playerid, $players_to_omit)) continue;
                    $row = array();
                    $row["playerid"] = $playerid;
                    if ($playerid == $goalkeeper_id)
                    {
                        $row["playerpos"] = "Bramkarz";
                        array_push($ret, $row);
                        unset($toanalyse[\Utils\General::getKeyByValue($playerid, $toanalyse)]);
                        continue;
                    }

                    if ($this->isTheMostActions($playerid, "goal"))
                    {
                        if ($this->isTheMostActions($playerid, "accurate_shot"))
                        {
                            $row["playerpos"] = "Środkowy napastnik";
                            array_push($ret, $row);
                            unset($toanalyse[\Utils\General::getKeyByValue($playerid, $toanalyse)]);
                            continue;
                        }
                    }

                    if ($this->isTheMostActions($playerid, "assist"))
                    {
                        $row["playerpos"] = "Środkowy pomocnik";
                        array_push($ret, $row);
                        unset($toanalyse[\Utils\General::getKeyByValue($playerid, $toanalyse)]);
                        continue;
                    }
                    else
                    {
                        if ($this->isTheMostActions($playerid, "shot"))
                        {
                            $row["playerpos"] = "Skrzydłowy napastnik";
                            array_push($ret, $row);
                            unset($toanalyse[\Utils\General::getKeyByValue($playerid, $toanalyse)]);
                            continue;
                        }
                        else
                        {
                            if ($this->isTheMostActions($playerid, "faul"))
                            {
                                if ($this->isTheMostActions($playerid, "overtake"))
                                {
                                    $row["playerpos"] = "Skrzydłowy obrońca";
                                    array_push($ret, $row);
                                    unset($toanalyse[\Utils\General::getKeyByValue($playerid, $toanalyse)]);
                                    continue;
                                }
                            }

                            if ($this->isTheMostActions($playerid, "offset"))
                            {
                                $row["playerpos"] = "Skrzydłowy obrońca";
                                array_push($ret, $row);
                                unset($toanalyse[\Utils\General::getKeyByValue($playerid, $toanalyse)]);
                                continue;
                            }
                            else
                            {
                                $row["playerpos"] = "Skrzydłowy pomocnik";
                                array_push($ret, $row);
                                unset($toanalyse[\Utils\General::getKeyByValue($playerid, $toanalyse)]);
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

        public function isCorrectionNeeded(array $result) : string
        {
            try
            {
                echo "<br><br>";
                var_dump($result);
                foreach ($this->positions as $position)
                {
                    $key = key($this->positions);
                    echo \Utils\General::countInNestedArrayByKey($result, "playerpos", $key);
                    echo "<br>".$position;
                    if (\Utils\General::countInNestedArrayByKey($result, "playerpos", $key) > $position) return $key;
                }

                return "";
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function doCorrection()
        {
            try
            {

            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getPlayersToAnalyse() : array
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

                /*echo "id = ".$userid." ";
                var_dump($collection);
                echo " ".$action;
                echo "<br>";*/
                //echo $userid." ".$action." ";
                //var_dump($collection);
                if (empty($collection))
                {
                    //var_dump(false);
                    return false;
                }

                $max = array_keys($collection, max($collection));
                //var_dump($max);
                //var_dump(in_array($userid, $max));
                //echo "max = ";
                //var_dump(in_array($userid, $max));
                //var_dump($max);

                //echo "<hr color='black' size='4'></hr>";
                //return $max == $userid;
                return in_array($userid, $max);
                //return $max[0] == $userid;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getByTheMostActions(array $players, string $action) : int
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

                $max = array_keys($collection, max($collection));
                //var_dump($max);
                //echo "<br>";
                return 1;
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
    if (isset($_GET["test"]))
    {
        try
        {
            $expert = new \Expert\FootballExpert(4);
            /*$players = $expert->getPlayersToAnalyse();
            $x = $expert->getByTheMostActions($players, "goal");

            $expert->getByTheMostActions($players, "goal");*/
            //var_dump($expert->isTheMostActions(5, "goal"));
            $ret = $expert->doAnalyse(12);
            var_dump($ret);
            foreach ($ret as $player)
            {
                $user = new \User\LoggedUser($player["playerid"]);
                echo "<span style='font-weight: bold;'>".$user->getUserCredentials()."</span> został wytypowany na pozycję <span style='font-weight: bold;'>".\Utils\Dictionary::keyToWord($player["playerpos"])."</span><br>";
            }

            echo $expert->isCorrectionNeeded($ret);
        }
        catch (\Exception $e)
        {
            echo $e->getMessage();
        }
    }
}