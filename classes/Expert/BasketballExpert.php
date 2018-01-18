<?php

namespace
{
    require_once("Expert.php");
}

namespace Expert
{
    class BasketballExpert extends Expert
    {
        private $data;
        private $analyse_buffer; // used to collect already choosen players
        private $positions = array();
        public function __construct(int $team_id)
        {
            try
            {
                parent::__construct($team_id);
                $this->data = parent::getAllTeamPlayersActions();

                $this->positions["Rozgrywający"] = 1;
                $this->positions["Obrońca"] = 1;
                $this->positions["Niski skrzydłowy"] = 1;
                $this->positions["Silny skrzydłowy"] = 1;
                $this->positions["Center"] = 1;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function prepareDataToAnalyse() : array
        {
            $players_ids = $this->getPlayersToAnalyse();
            $toanalyse = array();
            foreach ($players_ids as $player_id)
            {
                $row = array();
                $row["player_id"] = $player_id; // needed only to identify by index

                $subrow = array();
                $subrow["pass"] = 0;
                $subrow["dribble"] = 0;
                $subrow["accurate_shot_basket"] = 0;
                $subrow["catch"] = 0;
                $subrow["3points"] = 0;
                $subrow["basketaction"] = 0;

                // Actions that don't take part in analyse
                $subrow["defence"] = 0;
                $subrow["overtake"] = 0;
                $subrow["counter"] = 0;
                $subrow["faul"] = 0;
                $subrow["assist"] = 0;

                array_push($row, $subrow);
                array_push($toanalyse, $row);
            }

            return $toanalyse;
        }

        private function parseData(array $raw_players) : array
        {
            foreach ($this->data as $row)
            {
                foreach ($raw_players as &$player)
                {
                    /*
                     * Structure:
                     * arr
                     * {
                     *  arr[0]
                     *  {
                     *      arr["player_id"]
                     *      arr[0]
                     *      {
                     *          ["goal"] = 0
                     *          ["assist"] = 0
                     *          etc...
                     *      }
                     *  }
                     * }
                     */
                    if ($player["player_id"] == $row["user_id"] && isset($player[0][$row["basketball_action"]])) $player[0][$row["basketball_action"]]++;
                }
            }
            return $raw_players;
        }

        private function getMaxOfActions(array $parsed_data, string $action) : array
        {
            $max = 0;
            $max_id = 0;
            foreach ($parsed_data as $player)
            {
                if ($player[0][$action] > $max)
                {
                    $max = $player[0][$action];
                    $max_id = $player["player_id"];
                }
            }

            $ret = array();
            $ret["max"] = $max;
            $ret["max_id"] = $max_id;
            return $ret;
        }

        public function doAnalyse(array $players_to_omit = null) : array
        {
            try
            {
                if (!is_null($players_to_omit) && count($this->team->getAllTeamPlayers()) - count($players_to_omit) < 5) throw new \Exception("Drużyna musi składać się z przynajmniej 11 graczy!");
                $players = $this->prepareDataToAnalyse();
                $toanalyse = $this->parseData($players);

                if (!is_null($players_to_omit))
                {
                    $counter = -1;
                    foreach ($toanalyse as $player)
                    {
                        $counter++;
                        if (in_array($player["player_id"], $players_to_omit))
                        {
                            unset($toanalyse[$counter]);
                            $toanalyse = array_values($toanalyse);
                        }
                    }
                }

                $ret = array();

                //region Rozgrywający

                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                $facts = "";
                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["pass"] + $player[0]["dribble"] > $max)
                    {
                        $max = $player[0]["pass"] + $player[0]["dribble"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".\Utils\Dictionary::keyToWord($action).", ".$action_occurences.")";//$facts .= "(".$action_occurences.", ".\Utils\Dictionary::keyToWord($action).", ";
                            }
                        }
                    }
                }
                $row = array();
                $row["player_pos"] = "Rozgrywający";
                $row["player_id"] = $maxid;
                $row["how"] = "Najwięcej podań i udanych kozłów";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz jest rezerwowym ? NIE<br>";
                $row["rules"] .= "Czy suma podań i udanych kozłów jest najwyższa ? TAK<br>";

                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);

                //endregion

                //region Obrońca

                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                $facts = "";
                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["accurate_shot_basket"] > $max)
                    {
                        $max = $player[0]["accurate_shot_basket"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".\Utils\Dictionary::keyToWord($action).", ".$action_occurences.")";
                            }
                        }
                    }
                }
                $row = array();
                $row["player_pos"] = "Obrońca";
                $row["player_id"] = $maxid;
                $row["how"] = "Najwięcej celnych rzutów";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz jest rezerwowym ? NIE<br>";
                $row["rules"] .= "Czy suma podań i udanych kozłów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej celnych rzutów ? TAK<br>";

                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);

                //endregion

                //region Niski Skrzydłowy

                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                $facts = "";
                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["catch"] + $player[0]["3points"] > $max)
                    {
                        $max = $player[0]["catch"] + $player[0]["3points"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".\Utils\Dictionary::keyToWord($action).", ".$action_occurences.")";
                            }
                        }
                    }
                }
                $row = array();
                $row["player_pos"] = "Niski skrzydłowy";
                $row["player_id"] = $maxid;
                $row["how"] = "Najwięcej przechwytów i rzutów za 3 punkty";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz jest rezerwowym ? NIE<br>";
                $row["rules"] .= "Czy suma podań i udanych kozłów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej celnych rzutów ? NIE<br>";
                $row["rules"] .= "Czy suma przechwytów i rzutów za 3 punkty jest najwyższa ? TAK<br>";

                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);

                //endregion

                //region Center

                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                $facts = "";
                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["basketaction"]> $max)
                    {
                        $max = $player[0]["basketaction"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".\Utils\Dictionary::keyToWord($action).", ".$action_occurences.")";
                            }
                        }
                    }
                }
                $row = array();
                $row["player_pos"] = "Center";
                $row["player_id"] = $maxid;
                $row["how"] = "Najwięcej udanych podkoszowych akcji";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz jest rezerwowym ? NIE<br>";
                $row["rules"] .= "Czy suma podań i udanych kozłów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej celnych rzutów ? NIE<br>";
                $row["rules"] .= "Czy suma przechwytów i rzutów za 3 punkty jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy najwięcej udanych podkoszowych akcji ? TAK<br>";

                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);

                //endregion

                //region Silny Skrzydłowy

                $row = array();
                $row["player_id"] = $toanalyse[0]["player_id"];
                $row["player_pos"] = "Silny skrzydłowy";
                $row["how"] = "Pozostali zawodnicy";
                $row["facts"] = "";
                foreach ($toanalyse[0] as $actions)
                {
                    if (is_int($actions)) continue;
                    foreach ($actions as $action => $action_occurences)
                    {
                        if ($action_occurences > 0) $row["facts"] .= "(".\Utils\Dictionary::keyToWord($action).", ".$action_occurences.")";
                    }
                }

                $user = new \User\LoggedUser($toanalyse[0]["player_id"]);
                $row["credentials"] = $user->getUserCredentials();

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz jest rezerwowym ? NIE<br>";
                $row["rules"] .= "Czy suma podań i udanych kozłów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej celnych rzutów ? NIE<br>";
                $row["rules"] .= "Czy suma przechwytów i rzutów za 3 punkty jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy najwięcej udanych podkoszowych akcji ? NIE<br>";

                array_push($ret, $row);

                //endregion

                return $ret;
            }
            catch(\Exception $e)
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
    }
}
