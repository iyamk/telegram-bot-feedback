<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class CallbackqueryCommand extends SystemCommand
{
    public function execute(): ServerResponse
    {
        $callback_query = $this->getCallbackQuery();
        $callback_data  = $callback_query->getData();
        $chat_id = $callback_query->getMessage()->getChat()->getId();
        $message_id = $callback_query->getMessage()->getMessageId();
        $user_id = $callback_query->getFrom()->getId();

        $cm = new \App\Models\ConfigModel;
        $um = new \App\Models\UsersModel;
        $mm = new \App\Models\MessagesModel;
        $config = $cm->first();
        $perPage = config('Pager')->perPage;

        if ($callback_data == 'users_page_prev' || $callback_data == 'users_page_next')
        {
            Request::deleteMessage([ 'chat_id' => $chat_id, 'message_id' => $message_id ]);
            if ($callback_data == 'users_page_prev')
            {
                if ($config['page'] - 1 > 0)
                    $cm->where('id', 1)->set('page', 'page - 1', false)->update();
            }
            else
            {
                $count_pages = ceil($um->countAll() / $perPage);
                if ($config['page'] + 1 <= $count_pages)
                    $cm->where('id', 1)->set('page', 'page + 1', false)->update();
            }
            \Longman\TelegramBot\Commands\SystemCommands\GenericmessageCommand::show_users($user_id);
        }

        if ($callback_data == 'messages_page_prev' || $callback_data == 'messages_page_next')
        {
            Request::deleteMessage([ 'chat_id' => $chat_id, 'message_id' => $message_id ]);
            if ($callback_data == 'messages_page_prev')
            {
                if ($config['page'] - 1 > 0)
                    $cm->where('id', 1)->set('page', 'page - 1', false)->update();
            }
            else
            {
                $count_pages = ceil($mm->countAll() / $perPage);
                if ($config['page'] + 1 <= $count_pages)
                    $cm->where('id', 1)->set('page', 'page + 1', false)->update();
            }
            \Longman\TelegramBot\Commands\SystemCommands\GenericmessageCommand::show_messages($user_id);
        }

        return Request::emptyResponse();
    }
}