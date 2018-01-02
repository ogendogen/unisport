<?php

namespace {
    require_once(__DIR__."/../Db/Database.php");
    require_once(__DIR__."/../Team/Team.php");
    require_once(__DIR__."/../Utils/General.php");
    require_once(__DIR__."/../Utils/Dictionary.php");
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
                return $this->db->exec("SELECT user_id, user_name, user_surname, football_action FROM `games_players` 
                                              INNER JOIN `games_players_football_info` ON games_players.player_id = games_players_football_info.football_gameplayerid 
                                              INNER JOIN `users` ON games_players.player_playerid = users.user_id
                                              WHERE games_players.player_teamid = ?", [$this->team_id]);
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        private function checkRequirements()
        {
            try
            {
                $ret = $this->db->exec("SELECT COUNT(*) AS 'amount', users.user_name, users.user_surname FROM `games_players` 
                                            INNER JOIN `games_players_football_info` ON games_players.player_id = games_players_football_info.football_gameplayerid
                                            INNER JOIN `users` ON games_players.player_playerid = users.user_id 
                                            WHERE games_players.player_teamid = ?
                                            GROUP BY user_id", [$this->team_id]);
                if (empty($ret)) throw new \Exception("Brakuje danych lub zawodników do przeprowadzenia analizy!");
                foreach($ret as $row)
                {
                    if (intval($row["amount"]) < 4) throw new \Exception("Za mało danych! Brakuje danych dla: ". $row["user_name"]." ".$row["user_surname"]);
                }
                return true;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}