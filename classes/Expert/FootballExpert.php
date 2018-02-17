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
        public function __construct(int $team_id)
        {
            try
            {
                parent::__construct($team_id);
                $this->data = parent::getAllTeamPlayersActions();
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
                    if ($player[0]["Gol"] + $player[0]["Celny strzał"] > $max)
                    {
                        $max = $player[0]["Gol"] + $player[0]["Celny strzał"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".$action.", ".$action_occurences.")";
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

                if ($maxid == 0) throw new \Exception("Niepowodzenie wnioskowania. Za mało akcji: Gol, Celny Strzał.".(!is_null($players_to_omit) ? " Spróbuj również zmienić rezerwowych." : ""));
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
                    if ($player[0]["Asysta"] > $max)
                    {
                        $max = $player[0]["Asysta"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".$action.", ".$action_occurences.")";
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

                if ($maxid == 0) throw new \Exception("Niepowodzenie wnioskowania. Za mało akcji: Asysta.".(!is_null($players_to_omit) ? "Spróbuj również zmienić rezerwowych." : ""));
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
                    if ($player[0]["Strzał"] > $max)
                    {
                        $max = $player[0]["Strzał"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".$action.", ".$action_occurences.")";
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

                if ($maxid == 0) throw new \Exception("Niepowodzenie wnioskowania. Za mało akcji: Strzał.".(!is_null($players_to_omit) ? "Spróbuj również zmienić rezerwowych." : ""));
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
                    if ($player[0]["Strzał"] > $max)
                    {
                        $max = $player[0]["Strzał"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".$action.", ".$action_occurences.")";
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

                if ($maxid == 0) throw new \Exception("Niepowodzenie wnioskowania. Za mało akcji: Strzał.".(!is_null($players_to_omit) ? " Spróbuj również zmienić rezerwowych." : ""));
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
                    if ($player[0]["Faul"] + $player[0]["Przejęcie"] > $max)
                    {
                        $max = $player[0]["Faul"] + $player[0]["Przejęcie"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".$action.", ".$action_occurences.")";
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

                if ($maxid == 0) throw new \Exception("Niepowodzenie wnioskowania. Za mało akcji: Faul, Przejęcie.".(!is_null($players_to_omit) ? " Spróbuj również zmienić rezerwowych." : ""));
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
                    if ($player[0]["Faul"] + $player[0]["Przejęcie"] > $max)
                    {
                        $max = $player[0]["Faul"] + $player[0]["Przejęcie"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".$action.", ".$action_occurences.")";
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

                if ($maxid == 0) throw new \Exception("Niepowodzenie wnioskowania. Za mało akcji: Faul, Przejęcie.".(!is_null($players_to_omit) ? " Spróbuj również zmienić rezerwowych." : ""));
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
                    if ($player[0]["Faul"] > $max)
                    {
                        $max = $player[0]["Faul"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            $facts = "";
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".$action.", ".$action_occurences.")";
                            }
                        }
                        $is_faul = true;
                    }
                    else if ($player[0]["Spalony"] > $max)
                    {
                        $max = $player[0]["Spalony"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;
                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            $facts = "";
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".$action.", ".$action_occurences.")";
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

                if ($maxid == 0) throw new \Exception("Niepowodzenie wnioskowania. Za mało akcji: Faul, Spalony.".(!is_null($players_to_omit) ? " Spróbuj również zmienić rezerwowych." : ""));
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
                    if ($player[0]["Faul"] > $max)
                    {
                        $max = $player[0]["Faul"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            $facts = "";
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".$action.", ".$action_occurences.")";
                            }
                        }
                    }
                    else if ($player[0]["Spalony"] > $max)
                    {
                        $max = $player[0]["Spalony"];
                        $maxid = $player["player_id"];
                        $player_to_delete = $counter;

                        foreach ($player as $actions)
                        {
                            if (is_int($actions)) continue;
                            $facts = "";
                            foreach ($actions as $action => $action_occurences)
                            {
                                if ($action_occurences > 0) $facts .= "(".$action.", ".$action_occurences.")";
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

                if ($maxid == 0) throw new \Exception("Niepowodzenie wnioskowania. Za mało akcji: Faul, Spalony.".(!is_null($players_to_omit) ? " Spróbuj również zmienić rezerwowych." : ""));
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
                        if ($action_occurences > 0) $row["facts"] .= "(".$action.", ".$action_occurences.")";
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
                        if ($action_occurences > 0) $row["facts"] .= "(".$action.", ".$action_occurences.")";
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

        private function prepareDataToAnalyse() : array
        {
            $players_ids = $this->getPlayersToAnalyse();
            $toanalyse = array();
            foreach ($players_ids as $player_id)
            {
                $row = array();
                $row["player_id"] = $player_id; // needed only to identify by index

                $subrow = array();
                $subrow["Gol"] = 0;
                $subrow["Celny strzał"] = 0;
                $subrow["Asysta"] = 0;
                $subrow["Strzał"] = 0;
                $subrow["Faul"] = 0;
                $subrow["Przejęcie"] = 0;
                $subrow["Spalony"] = 0;

                // Actions that don't take part in analyse
                $subrow["Obrona"] = 0;
                $subrow["Kontra"] = 0;

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
                    if ($player["player_id"] == $row["user_id"] && isset($player[0][$row["actions_action"]])) $player[0][$row["actions_action"]]++;
                }
            }
            return $raw_players;
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
    }
}