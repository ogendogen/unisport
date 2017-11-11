<?php

namespace {
    require_once("Team.php");
    require_once(__DIR__."/../Db/Database.php");
    require_once("Sport.php");
    require_once(__DIR__."/../User/LoggedUser.php");
}

namespace Team {
    class Game
    {
        private $db;
        private $game_id;
        private $team1;
        private $team2;
        private $date;
        private $generaldesc;
        private $team1_players; // array of userids

        public function __construct(int $game_id)
        {
            $this->game_id = $game_id;
            $this->db = \Db\Database::getInstance();
            if ($this->game_id > 0) {
                $arr = $this->getGameData();
                $this->team1 = $arr["game_team1id"];
                $this->team2 = $arr["game_team2id"];
                $this->date = $arr["game_date"];
                $this->generaldesc = $arr["game_generaldesc"];
                if (!is_array($arr["game_team1players"])) throw new \Exception("Niepoprawna lista uczestników meczu!");
                $this->team1_players = $arr["game_team1players"];
            }
        }

        public static function createNewGame(int $team1_id, int $team2_id, int $date, string $generaldesc, array $team1_players, int $team1_score, int $team2_score): Game // returns game object if succeded
        {
            try {
                $db = \Db\Database::getInstance();
                $team1 = new \Team\Team($team1_id);
                $team2 = new \Team\Team($team2_id);
                if (!$team1->isTeamExists()) throw new \Exception("Pierwsza drużyna nie istnieje!");
                if (!$team2->isTeamExists()) throw new \Exception("Druga drużyna nie istnieje!");
                if (!is_integer($team1_score) || $team1_score < 0) throw new \Exception("Wynik twojej drużyny jest niepoprawny!");
                if (!is_integer($team2_score) || $team2_score < 0) throw new \Exception("Wynik drużyny przeciwnej jest niepoprawny!");
                if ($date > time()) throw new \Exception("Data meczu jeszcze nie nastąpiła!");

                $db->exec("INSERT INTO games SET game_team1id = ?,
                                          game_team2id = ?,
                                          game_team1score = ?,
                                          game_team2score = ?,
                                          game_date = ?,
                                          game_generaldesc = ?", [$team1_id, $team2_id, $team1_score, $team2_score, $date, $generaldesc]);

                $game_id = $db->exec("SELECT MAX(game_id) AS 'game_id' FROM games")[0]["game_id"];

                foreach ($team1_players as $playerid) {
                    $user = new \User\User();
                    if (!$user->isUserExistsById($playerid)) throw new \Exception("Jeden z uczestników meczu nie istnieje!");
                    if (!$team1->isUserInTeam($playerid)) throw new \Exception("Ten gracz nie należy do drużyny!");

                    $db->exec("INSERT INTO games_players SET player_gameid = ?,
                                                player_teamid = ?,
                                                player_playerid = ?", [$game_id, $team1_id, $playerid]);
                }

                return new Game($game_id);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public static function getLast10UserGames(int $user_id, int $team_id)
        {
            try
            {
                $db = \Db\Database::getInstance();
                $user = new \User\LoggedUser($user_id);
                $myteam = new \Team\Team($team_id);
                if (!$user->isUserExistsById($user_id)) throw new \Exception("Taki użytkownik nie istnieje!");
                if (!$myteam->isTeamExists()) throw new \Exception("Taka drużyna nie istnieje!");

                $games = $db->exec("SELECT * FROM `games` WHERE game_team1id = ? ORDER BY game_date DESC LIMIT 10", [$team_id]);
                if (!$games) return null; // no games

                return $games;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public static function getAllUserGames(int $user_id, int $team_id)
        {
            try
            {
                $db = \Db\Database::getInstance();
                $user = new \User\LoggedUser($user_id);
                $myteam = new \Team\Team($team_id);
                if (!$user->isUserExistsById($user_id)) throw new \Exception("Taki użytkownik nie istnieje!");
                if (!$myteam->isTeamExists()) throw new \Exception("Taka drużyna nie istnieje!");

                $games = $db->exec("SELECT * FROM `games` WHERE game_team1id = ? ORDER BY game_date DESC", [$team_id]);
                if (!$games) return null; // no games

                return $games;
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getGameData(): array // returns array of basic data about game and a jagged array with players ids
        {
            if ($this->game_id == 0 || !$this->isGameExists()) throw new \Exception("Taki mecz nie istnieje!");

            $gamedata = $this->db->exec("SELECT * FROM games WHERE game_id = ?", [$this->game_id])[0];
            $gamedata["game_team1players"] = array();
            //$gamedata["game_team2players"] = array();

            $team1_players = $this->db->exec("SELECT player_playerid FROM games_players WHERE player_teamid = ? AND player_gameid = ?", [$gamedata["game_team1id"], $this->game_id]);
            foreach ($team1_players as $player) array_push($gamedata["game_team1players"], $player["player_playerid"]);

            //$team2_players = $this->db->exec("SELECT player_playerid FROM games_players WHERE player_teamid = ? AND player_gameid = ?", [$gamedata["game_team2id"], $this->game_id]);
            //foreach ($team2_players as $player) array_push($gamedata["game_team2players"], $player["player_playerid"]);

            //$gamedata["game_team1players"] = $this->db->exec("SELECT * FROM games_players WHERE player_teamid = ?", [$gamedata["game_team1id"]]);
            //$gamedata["game_team2players"] = $this->db->exec("SELECT * FROM games_players WHERE player_teamid = ?", [$gamedata["game_team2id"]]);

            var_dump($gamedata);
            return $gamedata;
            //$gamedata = $this->db->exec("SELECT games.*, games_players.* FROM games LEFT JOIN games_players ON games.id = player.gameid WHERE games.game_id = ?", [$this->match_id])[0];
        }

        public function isGameExists(): bool
        {
            if ($this->game_id == 0) return false;
            return $this->db->isRowExists("game_id", "games", $this->game_id);
        }

        public function addPlayerToGame(bool $is_team1, int $player_id)
        {
            try
            {
                $teamid = ($is_team1 ? $this->team1 : $this->team2);
                $user = new \User\LoggedUser($player_id); // checks if user exists

                $this->db->exec("INSERT INTO games_players SET player_teamid = ?,
                                                 player_playerid = ?,
                                                 player_gameid = ?", [$teamid, $player_id, $this->game_id]);
            }
            catch (\Exception $e)
            {
                if ($e->getCode() == 23000) throw new \Exception("Ten użytkownik jest już zapisany w tym meczu!");
                throw $e;
            }
        }

        public function addAction(int $player_id, string $action_name, int $action_minute, int $action_second, bool $is_team1)
        {
            try
            {
                // exception with -1 code is a fatal error, exception with 0 code is a warning and continuation is acceptable
                if (is_null($this->game_id) || $this->game_id == 0) throw new \Exception("Taka mecz nie istnieje!", -1);
                $player = new \User\LoggedUser($player_id);

                $team = new \Team\Team(($is_team1 ? $this->team1 : $this->team2));
                $is_football = ($team->getTeamInfo()["team_sport"] == "1" ? true : false);
                if (!$team->isUserInTeam($player_id)) throw new \Exception("Gracz nie należy do drużyny!", 0);
                if (!in_array($player_id, ($is_team1 ? $this->team1_players : $this->team2_players))) throw new \Exception("Gracz nie bierze udziału w tym meczu!");

                if ($action_minute < 0 || $action_minute > 120) throw new \Exception("Minuta akcji jest spoza zakresu!", 0);
                if ($action_second < 0 || $action_second > 59) throw new \Exception("Sekundy akcji są spoza zakresu!", 0);

                $actions = $this->db->getEnumPossibleValues(($is_football ? "games_players_football_info" : "games_players_general_info"), ($is_football ? "football_action" : "general_action"));
                if (!in_array($action_name, $actions)) throw new \Exception("Taka akcja nie istnieje!", 0);

                $gameplayerid = null;
                $gameplayerid = $this->db->exec("SELECT player_id FROM games_players WHERE player_gameid = ? AND player_teamid = ? AND player_playerid = ?", [$this->game_id, ($is_team1 ? $this->team1 : $this->team2), $player_id])[0]["player_id"];

                if (!$gameplayerid) throw new \Exception("Problem z dopasowaniem gracza do meczu!");
                if ($is_football) {
                    $this->db->exec("INSERT INTO games_players_football_info SET
                                                football_gameplayerid = ?,
                                                football_action = ?,
                                                football_minute = ?,
                                                football_second = ?", [$gameplayerid, $action_name, $action_minute, $action_second]);
                } else {
                    $this->db->exec("INSERT INTO games_players_general_info SET
                                                generalgame_gameplayerid = ?,
                                                general_action  = ?,
                                                general_minute = ?,
                                                general_second = ?", [$gameplayerid, $action_name, $action_minute, $action_second]);
                }
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}

?>