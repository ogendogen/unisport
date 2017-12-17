<?php

namespace {
    require_once(__DIR__."/../Db/Database.php");
    require_once(__DIR__."/../Team/Team.php");
}

namespace Expert
{
    class Expert
    {
        private $db;
        private $team;
        private $team_id;

        public function __construct(int $team_id)
        {
            try
            {
                $this->db = \Db\Database::getInstance();

                $this->team_id = $team_id;
                $this->team = new \Team\Team($team_id);
                if (!$this->team->isTeamExists()) throw new \Exception("Drużyna nie istnieje!");
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getAllTeamPlayersActions(): array
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

        public function findPosition() : string
        {
            try
            {

            }
            catch (\Exception $e)
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
                if (empty($ret)) return false;
                foreach($ret as $row)
                {
                    if (intval($row["amount"]) < 2) throw new \Exception("Za mało danych! Brakuje danych dla: ". $row["user_name"]." ".$row["user_surname"]);
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

namespace
{
    try
    {
        $expert = new \Expert\Expert(4);

        //echo var_dump($expert->getAllTeamPlayersActions());
    }
    catch (\Exception $e)
    {
        echo $e->getMessage();
    }
}