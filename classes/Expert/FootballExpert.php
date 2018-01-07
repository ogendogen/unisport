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
                $subrow["goal"] = 0;
                $subrow["accurate_shot"] = 0;
                $subrow["assist"] = 0;
                $subrow["shot"] = 0;
                $subrow["faul"] = 0;
                $subrow["overtake"] = 0;
                $subrow["offside"] = 0;

                // Actions that don't take part in analyse
                $subrow["defence"] = 0;
                $subrow["overtake"] = 0;
                $subrow["counter"] = 0;

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
                    if ($player["player_id"] == $row["user_id"] && isset($player[0][$row["football_action"]])) $player[0][$row["football_action"]]++;
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

        /*private function parseFacts(array $action) : array
        {
            try
            {
                var_dump($action);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }*/

        public function doAnalyse(int $goalkeeper_id, array $players_to_omit = null) : array
        {
            try
            {
                if (!$this->team->isUserInTeam($goalkeeper_id)) throw new \Exception("Proponowany bramkarz nie jest w drużynie!");
                if (!is_null($players_to_omit) && count($this->team->getAllTeamPlayers()) - count($players_to_omit) < 11) throw new \Exception("Drużyna musi składać się z przynajmniej 11 graczy!");
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

                //region Bramkarz

                $row["player_id"] = $goalkeeper_id;
                $row["player_pos"] = "Bramkarz";
                $row["how"] = "Wybrany przez użytkownika";
                $row["facts"] = "Nie brane pod uwagę";

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz nie jest rezerwowym ? TAK<br>";
                $row["rules"] .= "Czy gracz jest bramkarzem ? TAK<br>";

                $user = new \User\LoggedUser($goalkeeper_id);
                $row["credentials"] = $user->getUserCredentials();
                array_push($ret, $row);

                $to_delete = array_search($goalkeeper_id, $toanalyse); // will always find, because we check above whether goalkeeper is in team
                unset($toanalyse[$to_delete]);
                $toanalyse = array_values($toanalyse);

                //endregion

                //region Środkowy napastnik
                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                $facts = "";
                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["goal"] + $player[0]["accurate_shot"] > $max)
                    {
                        $max = $player[0]["goal"] + $player[0]["accurate_shot"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                            }
                        }
                    }
                }
                $row = array();
                $row["player_pos"] = "Środkowy napastnik";
                $row["player_id"] = $maxid;
                $row["how"] = "Najwięcej goli i celnych strzałów";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz nie jest rezerwowym ? TAK<br>";
                $row["rules"] .= "Czy gracz jest bramkarzem ? NIE<br>";
                $row["rules"] .= "Czy suma goli i celnych strzałów jest najwyższa ? TAK<br>";

                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);
                //endregion

                //region Środkowy pomocnik
                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                $facts = "";
                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["assist"] > $max)
                    {
                        $max = $player[0]["assist"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                            }
                        }
                    }
                }
                $row = array();
                $row["player_pos"] = "Środkowy pomocnik";
                $row["player_id"] = $maxid;
                $row["how"] = "Najwięcej asyst";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz nie jest rezerwowym ? TAK<br>";
                $row["rules"] .= "Czy gracz jest bramkarzem ? NIE<br>";
                $row["rules"] .= "Czy suma goli i celnych strzałów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej asyst ? TAK<br>";

                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);
                //endregion

                //region Skrzydłowy napastnik x1
                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                $facts = "";
                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["shot"] > $max)
                    {
                        $max = $player[0]["shot"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                            }
                        }
                    }
                }
                $row = array();
                $row["player_pos"] = "Skrzydłowy napastnik";
                $row["player_id"] = $maxid;
                $row["how"] = "Najwięcej strzałów";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz nie jest rezerwowym ? TAK<br>";
                $row["rules"] .= "Czy gracz jest bramkarzem ? NIE<br>";
                $row["rules"] .= "Czy suma goli i celnych strzałów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej asyst ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej strzałów na bramkę ? TAK<br>";


                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);
                //endregion

                //region Skrzydłowy napastnik x2
                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                $facts = "";
                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["shot"] > $max)
                    {
                        $max = $player[0]["shot"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                            }
                        }
                    }
                }
                $row = array();
                $row["player_pos"] = "Skrzydłowy napastnik";
                $row["player_id"] = $maxid;
                $row["how"] = "Najwięcej strzałów";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz nie jest rezerwowym ? TAK<br>";
                $row["rules"] .= "Czy gracz jest bramkarzem ? NIE<br>";
                $row["rules"] .= "Czy suma goli i celnych strzałów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej asyst ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej strzałów na bramkę ? TAK<br>";

                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);
                //endregion

                //region Środkowy obrońca x1
                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                $facts = "";
                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["faul"] + $player[0]["overtake"] > $max)
                    {
                        $max = $player[0]["faul"] + $player[0]["overtake"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                            }
                        }
                    }
                }
                $row = array();
                $row["player_pos"] = "Środkowy obrońca";
                $row["player_id"] = $maxid;
                $row["how"] = "Największa ilość fauli i przejęć";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz nie jest rezerwowym ? TAK<br>";
                $row["rules"] .= "Czy gracz jest bramkarzem ? NIE<br>";
                $row["rules"] .= "Czy suma goli i celnych strzałów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej asyst ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej strzałów na bramkę ? NIE<br>";
                $row["rules"] .= "Czy suma fauli i przejęć jest najwyższa ? TAK<br>";

                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);
                //endregion

                //region Środkowy obrońca x2
                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                $facts = "";
                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["faul"] + $player[0]["overtake"] > $max)
                    {
                        $max = $player[0]["faul"] + $player[0]["overtake"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                            }
                        }
                    }
                }
                $row = array();
                $row["player_pos"] = "Środkowy obrońca";
                $row["player_id"] = $maxid;
                $row["how"] = "Największa ilość fauli i przejęć";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz nie jest rezerwowym ? TAK<br>";
                $row["rules"] .= "Czy gracz jest bramkarzem ? NIE<br>";
                $row["rules"] .= "Czy suma goli i celnych strzałów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej asyst ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej strzałów na bramkę ? NIE<br>";
                $row["rules"] .= "Czy suma fauli i przejęć jest najwyższa ? TAK<br>";

                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);
                //endregion

                //region Skrzydłowy obrońca x1
                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                $facts = "";
                $is_faul = true;

                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["faul"] > $max)
                    {
                        $max = $player[0]["faul"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            $facts = "";
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                            }
                        }
                        $is_faul = true;
                    }
                    else if ($player[0]["offside"] > $max)
                    {
                        $max = $player[0]["offside"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;
                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            $facts = "";
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                            }
                        }
                        $is_faul = false;
                    }
                }
                $row = array();
                $row["player_pos"] = "Skrzydłowy obrońca";
                $row["player_id"] = $maxid;
                $row["how"] = "Najwięcej fauli lub spalonych";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz nie jest rezerwowym ? TAK<br>";
                $row["rules"] .= "Czy gracz jest bramkarzem ? NIE<br>";
                $row["rules"] .= "Czy suma goli i celnych strzałów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej asyst ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej strzałów na bramkę ? NIE<br>";
                $row["rules"] .= "Czy suma fauli i przejęć jest najwyższa ? NIE<br>";
                $row["rules"] .= ($is_faul ? "Czy wykonał najwięcej fauli ? TAK<br>" : "Czy wykonał najwięcej fauli ? NIE<br>Czy wykonał najwięcej spalonych ? TAK");

                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);
                //endregion

                //region Skrzydłowy obrońca x2
                $max = 0;
                $maxid = 0;
                $counter = -1;
                $player_to_delete = -1;
                foreach ($toanalyse as $player)
                {
                    $counter++;
                    if ($player[0]["faul"] > $max)
                    {
                        $max = $player[0]["faul"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            $facts = "";
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                            }
                        }
                    }
                    else if ($player[0]["offside"] > $max)
                    {
                        $max = $player[0]["offside"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            $facts = "";
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                            }
                        }
                    }
                }
                $row = array();
                $row["player_pos"] = "Skrzydłowy obrońca";
                $row["player_id"] = $maxid;
                $row["how"] = "Najwięcej fauli lub spalonych";
                $row["facts"] = $facts;

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz nie jest rezerwowym ? TAK<br>";
                $row["rules"] .= "Czy gracz jest bramkarzem ? NIE<br>";
                $row["rules"] .= "Czy suma goli i celnych strzałów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej asyst ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej strzałów na bramkę ? NIE<br>";
                $row["rules"] .= "Czy suma fauli i przejęć jest najwyższa ? NIE<br>";
                $row["rules"] .= ($is_faul ? "Czy wykonał najwięcej fauli ? TAK<br>" : "Czy wykonał najwięcej fauli ? NIE<br>Czy wykonał najwięcej spalonych ? TAK");

                $user = new \User\LoggedUser($maxid);
                $row["credentials"] = $user->getUserCredentials();

                array_push($ret, $row);
                unset($toanalyse[$player_to_delete]);
                $toanalyse = array_values($toanalyse);
                //endregion

                //region Skrzydłowy pomocnik

                $row = array();
                $row["player_id"] = $toanalyse[0]["player_id"];
                $row["player_pos"] = "Skrzydłowy pomocnik";
                $row["how"] = "Pozostali zawodnicy";
                $row["facts"] = "";
                foreach ($toanalyse[0] as $actions)
                {
                    if (is_int($actions)) continue;
                    foreach ($actions as $action => $action_occurences)
                    {
                        if ($action_occurences > 0) $row["facts"] .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                    }
                }

                $user = new \User\LoggedUser($toanalyse[0]["player_id"]);
                $row["credentials"] = $user->getUserCredentials();

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz nie jest rezerwowym ? TAK<br>";
                $row["rules"] .= "Czy gracz jest bramkarzem ? NIE<br>";
                $row["rules"] .= "Czy suma goli i celnych strzałów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej asyst ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej strzałów na bramkę ? NIE<br>";
                $row["rules"] .= "Czy suma fauli i przejęć jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy wykonał najwięcej fauli ? NIE<br>";
                $row["rules"] .= "Czy wykonał najwięcej spalonych ? NIE";

                array_push($ret, $row);

                $row = array();
                $row["player_id"] = $toanalyse[1]["player_id"];
                $row["player_pos"] = "Skrzydłowy pomocnik";
                $row["how"] = "Pozostali zawodnicy";
                $row["facts"] = "";
                foreach ($toanalyse[1] as $actions)
                {
                    if (is_int($actions)) continue;
                    foreach ($actions as $action => $action_occurences)
                    {
                        if ($action_occurences > 0) $row["facts"] .= $action_occurences." ".\Utils\Dictionary::keyToWord($action).", ";
                    }
                }

                $user = new \User\LoggedUser($toanalyse[1]["player_id"]);
                $row["credentials"] = $user->getUserCredentials();

                $row["rules"] = "Czy gracz bierze udział ? TAK<br>";
                $row["rules"] .= "Czy gracz nie jest rezerwowym ? TAK<br>";
                $row["rules"] .= "Czy gracz jest bramkarzem ? NIE<br>";
                $row["rules"] .= "Czy suma goli i celnych strzałów jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej asyst ? NIE<br>";
                $row["rules"] .= "Czy gracz wykonał najwięcej strzałów na bramkę ? NIE<br>";
                $row["rules"] .= "Czy suma fauli i przejęć jest najwyższa ? NIE<br>";
                $row["rules"] .= "Czy wykonał najwięcej fauli ? NIE<br>";
                $row["rules"] .= "Czy wykonał najwięcej spalonych ? NIE";

                array_push($ret, $row);

                //endregion

                return $ret;
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        // to niżej idzie chyba do pieca XD

        public function doAnalyse_Naive(int $goalkeeper_id, array $players_to_omit = null) : array
        {
            try
            {
                if (!$this->team->isUserInTeam($goalkeeper_id)) throw new \Exception("Proponowany bramkarz nie jest w drużynie!");
                if (!is_null($players_to_omit) && count($this->team->getAllTeamPlayers()) - count($players_to_omit) < 11) throw new \Exception("Drużyna musi składać się z 11 graczy!");
                $ret = array(); // returning array with results
                $this->analyse_buffer = array();

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

                if (empty($collection)) return false;

                $max = array_keys($collection, max($collection));

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
            $ret = $expert->doAnalyse(12);

            foreach ($ret as $player)
            {
                echo "<span style='font-weight: bold;'>".$player["credentials"]."</span> został wytypowany na pozycję <span style='font-weight: bold;'>".\Utils\Dictionary::keyToWord($player["player_pos"])."</span><br>";
            }
        }
        catch (\Exception $e)
        {
            echo $e->getMessage();
        }
    }
}