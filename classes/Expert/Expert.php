<?php

namespace {
    require_once(__DIR__."/../Db/Database.php");
    require_once(__DIR__."/../Team/Team.php");
    require_once(__DIR__."/../Utils/General.php");
    require_once(__DIR__."/../User/LoggedUser.php");
}

namespace Expert
{
    class Expert
    {
        protected $db;
        protected $team;
        protected $team_id;

        protected function __construct(int $team_id)
        {
            try
            {
                $this->db = \Db\Database::getInstance();

                $this->team_id = $team_id;
                $this->team = new \Team\Team($team_id);
                if (!$this->team->isTeamExists()) throw new \Exception("Drużyna nie istnieje!");
                $this->checkRequirements();
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        protected function getAllTeamPlayersActions(): array
        {
            try
            {
                $this->checkRequirements();
                return $this->db->exec("SELECT user_id, user_name, user_surname, actions_action FROM `games_players` 
                                              INNER JOIN `games_players_actions` ON games_players.player_id = games_players_actions.actions_gameplayerid 
                                              INNER JOIN `users` ON games_players.player_playerid = users.user_id
                                              WHERE games_players.player_teamid = ?", [$this->team_id]);
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        private function checkRequirements() : bool
        {
            try
            {
                $ret = $this->db->exec("SELECT COUNT(*) AS 'amount', users.user_name, users.user_surname FROM `games_players` 
                                            INNER JOIN `games_players_actions` ON games_players.player_id = games_players_actions.actions_gameplayerid
                                            INNER JOIN `users` ON games_players.player_playerid = users.user_id 
                                            WHERE games_players.player_teamid = ?
                                            GROUP BY user_id", [$this->team_id]);
                if (empty($ret)) throw new \Exception("Brakuje danych lub zawodników do przeprowadzenia analizy!");
                if (count($ret) < count($this->team->getAllTeamPlayers())) throw new \Exception("Jeden lub więcej zawodników nie ma zapisanej żadnej akcji!");
                foreach($ret as $row)
                {
                    if (intval($row["amount"]) < 4) throw new \Exception("Za mało danych! Brakuje danych dla: ". $row["user_name"]." ".$row["user_surname"]);
                }

                $actions = $this->db->exec("SELECT COUNT(games_players_actions.actions_action) AS 'occurances', games_players_actions.actions_action FROM `games_players`
                                                    LEFT JOIN `games_players_actions` ON games_players.player_id = games_players_actions.actions_gameplayerid
                                                    WHERE games_players.player_teamid = ? AND games_players_actions.actions_action IS NOT NULL
                                                    GROUP BY games_players_actions.actions_action", [$this->team_id]);

                $doneactions = array();
                foreach($actions as $action)
                {
                    if ($action["occurances"] < 4) throw new \Exception("Drużyna wykonała za mało akcji: ". $action["actions_action"] ." (minimum 4)");
                    if (!in_array($action["actions_action"], $doneactions)) array_push($doneactions, $action["actions_action"]);
                }

                $rawdictionary = $this->db->exec("SELECT sport_dictionary_key FROM sport_dictionary WHERE sport_dictionary_sportid = ?", [$this->team->getTeamSportId()]);
                $dictionary = array();
                foreach ($rawdictionary as $dictrow)
                {
                    array_push($dictionary, $dictrow["sport_dictionary_key"]);
                }

                $intersected = array_diff($dictionary, $doneactions);
                if (!empty($intersected)) throw new \Exception("Drużyna nie wykonała żadnej akcji: ". implode(",", $intersected));

                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}