<?php


namespace User
{
    class Notification
    {
        private $db;
        private $notification_id;
        private $userid;
        private $action;
        private $url;

        public function __construct(int $notification_id)
        {
            try
            {
                $this->db = \Db\Database::getInstance();
                $data = $this->db->exec("SELECT notification_userid, notification_action, notification_url FROM notifications WHERE notification_id = ?", [$notification_id])[0];
                if (empty($data)) throw new \Exception("Takie powiadomienie nie istnieje!");

                $this->notification_id = $notification_id;
                $this->userid = $data["notification_userid"];
                $this->action = $data["notification_action"];
                $this->url = $data["notification_url"];
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function read()
        {
            try
            {
                $this->db->exec("UPDATE notifications SET notification_read = 1 WHERE notification_id = ?", [$this->notification_id]);
                $this->redirect();
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        private function redirect()
        {

        }

        public static function getAllUsersUnread(int $user_id) : array
        {
            try
            {
                $db = \Db\Database::getInstance();
                return $db->exec("SELECT notifications.notification_action, notifications.notification_url, notifications.notification_date,
                                           users.user_name, users.user_surname
                                           FROM notifications
                                           LEFT JOIN users ON notifications.notification_userid = users.user_id
                                           WHERE users.user_id = ?", [$user_id]);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public static function sendToUser(int $user_id, string $action, string $url)
        {
            try
            {
                $db = \Db\Database::getInstance();

                $user = new \User\User();
                if (!$user->isUserExistsById($user_id)) throw new \Exception("Taki użytkownik nie istnieje!");

                $db->exec("INSERT INTO notifications SET 
                                    notification_userid = ?,
                                    notification_action = ?,
                                    notification_url = ?", [$user_id, $action, $url]);
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        public static function sendToTeam(int $team_id, string $action, string $url)
        {
            try
            {
                $db = \Db\Database::getInstance();

                //$user = new \User\User();
                //if (!$user->isUserExistsById($user_id)) throw new \Exception("Taki użytkownik nie istnieje!");

                $team = new \Team\Team($team_id);
                if (!$team->isTeamExists()) throw new \Exception("Taka drużyna nie istnieje!");
                $players = $team->getAllTeamPlayers();

                foreach ($players as $player)
                {
                    $db->exec("INSERT INTO notifications SET 
                                    notification_userid = ?,
                                    notification_action = ?,
                                    notification_url = ?", [$player["user_id"], $action, $url]);
                }
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }
    }
}
