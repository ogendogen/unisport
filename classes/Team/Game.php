<?php

namespace {
    require_once("Team.php");
    require_once(__DIR__."/../Db/Database.php");
    require_once("Sport.php");
    require_once(__DIR__."/../User/LoggedUser.php");
}

namespace Team
{
    class Game
    {
        private $db;
        private $game_id;
        private $team1;
        private $team2;
        private $date;
        private $generaldesc;
        private $team1_players; // array of userids
        private $is_football;

        public function __construct(int $game_id)
        {
            $this->game_id = $game_id;
            $this->db = \Db\Database::getInstance();
            if ($this->game_id > 0) {
                $arr = $this->getGameData();
                $this->team1 = $arr["game_team1id"];

                $team1 = new \Team\Team($this->team1);
                $this->is_football = $team1->isFootballTeam();

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

        public function getAllGameActions()
        {
            try
            {
                return $this->db->exec("SELECT user_name, user_surname, actions_action, actions_minute, actions_second FROM `games_players` 
                                              INNER JOIN `games_players_actions` ON games_players.player_id = games_players_actions.actions_gameplayerid 
                                              INNER JOIN `users` ON games_players.player_playerid = users.user_id
                                              WHERE games_players.player_gameid = ?
                                              ORDER BY actions_minute ASC, actions_second ASC", [$this->game_id]);
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

            return $gamedata;
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

                if (!$team->isUserInTeam($player_id)) throw new \Exception("Gracz nie należy do drużyny!", 0);
                if (!in_array($player_id, ($is_team1 ? $this->team1_players : $this->team2_players))) throw new \Exception("Gracz nie bierze udziału w tym meczu!");

                if ($action_minute < 0 || $action_minute > 120) throw new \Exception("Minuta akcji jest z poza zakresu!", 0);
                if ($action_second < 0 || $action_second > 59) throw new \Exception("Sekundy akcji są z poza zakresu!", 0);

                $actions = array();
                $sport = new \Team\Sport($team->getTeamSportId());
                $actions = $sport->getAllSportActions();

                if (!in_array($action_name, $actions)) throw new \Exception("Taka akcja nie istnieje!", 0);

                $gameplayerid = null;
                $gameplayerid = $this->db->exec("SELECT player_id FROM games_players WHERE player_gameid = ? AND player_teamid = ? AND player_playerid = ?", [$this->game_id, ($is_team1 ? $this->team1 : $this->team2), $player_id])[0]["player_id"];

                if (!$gameplayerid) throw new \Exception("Problem z dopasowaniem gracza do meczu!");
                try
                {
                    $this->db->exec("INSERT INTO games_players_actions SET
                                                actions_gameplayerid = ?,
                                                actions_action = ?,
                                                actions_minute = ?,
                                                actions_second = ?", [$gameplayerid, $action_name, $action_minute, $action_second]);
                }
                catch (\Exception $e)
                {
                    if ($e->getCode() != 23000) throw $e; // rethrow only if exception isn't about doubled actions (unique key for 4 columns)
                }
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function editBasicData(int $opponent_id, int $game_time, int $team1_score, int $team2_score, string $game_report)
        {
            try
            {
                $team2 = new \Team\Team($opponent_id);
                if (!$team2->isTeamExists()) throw new \Exception("Druga drużyna nie istnieje!");
                if (!is_integer($team1_score) || $team1_score < 0) throw new \Exception("Wynik twojej drużyny jest niepoprawny!");
                if (!is_integer($team2_score) || $team2_score < 0) throw new \Exception("Wynik drużyny przeciwnej jest niepoprawny!");
                if ($game_time > time()) throw new \Exception("Data meczu jeszcze nie nastąpiła!");
                $this->db->exec("UPDATE games SET game_team2id = ?, game_date = ?, game_team1score = ?, game_team2score = ?, game_generaldesc = ? WHERE game_id = ?", [$opponent_id, $game_time, $team1_score, $team2_score, $game_report, $this->game_id]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function editGamePlayers(array $players)
        {
            try
            {
                if (empty($players)) return;
                $not_changed_players = array_intersect($this->team1_players, $players);
                $players_to_delete = array_diff($this->team1_players, $not_changed_players);
                $players_to_add = array_diff($players, $not_changed_players);

                // Database changes
                foreach ($players_to_delete as $player) $this->db->exec("DELETE FROM games_players WHERE player_playerid = ?", [$player]); // delete players
                foreach ($players_to_add as $player) $this->db->exec("INSERT INTO games_players SET player_gameid = ?, player_teamid = ?, player_playerid = ?", [$this->game_id, $this->team1, $player]); // add new players

                // Field changes
                $this->team1_players = $this->getGameData()["game_team1players"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function editGameActions(array $actions)
        {
            try
            {
                foreach ($actions as $action) $this->validateAction($action["player_id"], $action["action_name"], $action["action_minute"], $action["action_second"]);
                $this->deleteAllGameActions();
                $counter = 1;
                foreach ($actions as $action)
                {
                    $counter++;
                    $this->addAction($action["player_id"], $action["action_name"], $action["action_minute"], $action["action_second"], true);
                }
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function deleteAllGameActions()
        {
            try
            {
                $gameplayersids_raw = $this->db->exec("SELECT player_id FROM games_players WHERE player_gameid = ?", [$this->game_id]);
                $gameplayersids = array();
                foreach ($gameplayersids_raw as $id) array_push($gameplayersids, $id["player_id"]);
                //die("DELETE FROM games_players_actions WHERE actions_gameplayerid IN (". implode(',', $gameplayersids). ")");
                $this->db->exec("DELETE FROM games_players_actions WHERE actions_gameplayerid IN (?)", [implode(',', $gameplayersids)]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        private function validateAction(int $player_id, string $action_name, int $action_minute, int $action_second)
        {
            try
            {
                // exception with -1 code is a fatal error, exception with 0 code is a warning and continuation is acceptable
                if (is_null($this->game_id) || $this->game_id == 0) throw new \Exception("Taki mecz nie istnieje!", -1);
                $player = new \User\LoggedUser($player_id);

                $team = new \Team\Team($this->team1);
                if (!$team->isUserInTeam($player_id)) throw new \Exception("Gracz nie należy do drużyny!", 0);
                if (!in_array($player_id, ($this->team1_players))) throw new \Exception("Gracz nie bierze udziału w tym meczu!");

                if ($action_minute < 0 || $action_minute > 120) throw new \Exception("Minuta akcji jest spoza zakresu!", 0);
                if ($action_second < 0 || $action_second > 59) throw new \Exception("Sekundy akcji są spoza zakresu!", 0);

                $actions = array();
                $sport = new \Team\Sport($team->getTeamSportId());
                $actions = $sport->getAllSportActions();

                if (!in_array($action_name, $actions)) throw new \Exception("Taka akcja nie istnieje!", 0);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }
}

?>