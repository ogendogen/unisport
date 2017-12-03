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

        public function __construct(int $message_id)
        {
            try
            {
                $this->db = \Db\Database::getInstance();

                $data = $this->getAllMessageData();
                $this->message_id = $data["message_id"];
                $this->from_id = $data["message_sender"];
                $this->to_id = $data["message_receiver"];
                $this->title = $data["message_title"];
                $this->content = $data["message_text"];
                $this->read = ($data["message_read"] == "1" ? true : false);
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

                $user = new \User\User();
                $userdata = $user->getUserDataById($this->from_id);
                $ret["message_receiver"] = $userdata["user_name"]." ".$userdata["user_surname"];

                $userdata = $user->getUserDataById($this->to_id);
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

        public static function getAllUserMessages(int $user_id) : array
        {
            try
            {
                $db = \Db\Database::getInstance();

                $user = new \User\User();
                if (!$user->isUserExistsById($user_id)) throw new \Exception("Taki użytkownik nie istnieje!");
                return $db->exec("SELECT users.user_name, users.user_surname, messages.message_title, messages.message_text FROM `messages`
                                    LEFT JOIN `users` ON users.user_id = messages.message_receiver
                                    WHERE messages.message_id = ?", [$user_id]);
            }
            catch(\Exception $e)
            {
                throw $e;
            }
        }
    }
}