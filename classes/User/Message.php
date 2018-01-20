<?php

namespace {
    require_once(__DIR__."/../Db/Database.php");
}

namespace User
{
    class Message
    {
        private $db;
        private $message_id;
        private $from_id;
        private $to_id;
        private $title;
        private $content;
        private $read;
        private $sent;

        public function __construct(int $message_id, int $user_id)
        {
            try
            {
                $this->db = \Db\Database::getInstance();

                $data = $this->db->exec("SELECT * FROM messages WHERE message_id = ? AND message_receiver = ?", [$message_id, $user_id])[0];
                if (empty($data)) throw new \Exception("Taka wiadomość nie istnieje lub nie należy do ciebie!");

                $this->message_id = $data["message_id"];
                $this->from_id = $data["message_sender"];
                $this->to_id = $data["message_receiver"];
                $this->title = $data["message_title"];
                $this->content = $data["message_text"];
                $this->read = ($data["message_read"] == "1" ? true : false);
                $this->sent = $data["message_sent"];

                if ($this->to_id != $user_id) throw new \Exception("To nie jest twoja wiadomość!");
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public function getAllMessageData() : array
        {
            try
            {
                $ret = array();
                $ret["message_id"] = $this->message_id;
                $ret["message_title"] = $this->title;
                $ret["message_text"] = $this->content;
                $ret["message_read"] = $this->read;
                $ret["message_sent"] = $this->sent;

                $sender = new \User\LoggedUser($this->from_id);
                $userdata = $sender->getUserDataById($this->from_id);
                $ret["message_receiver"] = $userdata["user_name"]." ".$userdata["user_surname"];

                $receiver = new \User\LoggedUser($this->to_id);
                $userdata = $receiver->getUserDataById($this->to_id);
                $ret["message_sender"] = $userdata["user_name"]." ".$userdata["user_surname"];

                return $ret;
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        public function readMessage()
        {
            try
            {
                $this->db->exec("UPDATE messages SET message_read = 1 WHERE message_id = ?", [$this->message_id]);
                $this->read = true;
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        public function isRead() : bool
        {
            return $this->read;
        }

        public static function sendMessage(int $from, int $to, string $title, string $content) : int
        {
            try
            {
                $user = new \User\User();
                if (!$user->isUserExistsById($from)) throw new \Exception("Wysyłający nie istnieje!");
                if (!$user->isUserExistsById($to)) throw new \Exception("Odbiorca nie istnieje!");
                if (strlen($title) > 32) throw new \Exception("Tytuł wiadomości jest za długi! (maks 32 znaków)");

                $db = \Db\Database::getInstance();
                $db->exec("INSERT INTO messages SET 
                                          message_sender = ?,
                                          message_receiver = ?,
                                          message_title = ?,
                                          message_text = ?", [$from, $to, $title, $content]);

                return $db->getLastInsertId("message_id");
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }

        public static function countUnreadMessages(int $user_id) : int
        {
            try
            {
                $db = \Db\Database::getInstance();
                $ret = $db->exec("SELECT COUNT(*) AS 'messages' FROM messages WHERE message_receiver = ? AND message_read = 0", [$user_id])[0];
                return intval($ret["messages"]);
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        public static function getAllUserMessages(int $user_id) : array
        {
            try
            {
                $db = \Db\Database::getInstance();

                $user = new \User\User();
                if (!$user->isUserExistsById($user_id)) throw new \Exception("Taki użytkownik nie istnieje!");
                return $db->exec("SELECT users.user_name, users.user_surname, messages.message_title, messages.message_text, messages.message_sent, messages.message_read, messages.message_id FROM `messages`
                                    LEFT JOIN `users` ON users.user_id = messages.message_receiver
                                    WHERE users.user_id = ?", [$user_id]);
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        public static function getAllSentMessages(int $user_id) : array
        {
            try
            {
                $db = \Db\Database::getInstance();

                $user = new \User\User();
                if (!$user->isUserExistsById($user_id)) throw new \Exception("Taki użytkownik nie istnieje!");
                return $db->exec("SELECT users.user_name, users.user_surname, messages.message_title, messages.message_text, messages.message_sent, messages.message_read, messages.message_id FROM `messages`
                                    LEFT JOIN `users` ON users.user_id = messages.message_sender
                                    WHERE users.user_id = ?", [$user_id]);
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }

        public static function deleteMessages(int $user_id, array $msg_ids)
        {
            try
            {
                $db = \Db\Database::getInstance();
                $idsToDelete = implode($msg_ids, ', ');
                $db->exec("DELETE FROM messages WHERE message_receiver = ? AND message_id IN (".$idsToDelete.")");
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }
    }
}