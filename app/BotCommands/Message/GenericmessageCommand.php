<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class GenericmessageCommand extends SystemCommand
{
    protected $name = 'genericmessage';
    protected $description = 'Handle generic message';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $message_text = $message->getText(true);
        if (is_null($message_text)) return Request::emptyResponse();
        $user_id = $message->getFrom()->getId();

        $um = new \App\Models\UsersModel;
        $mm = new \App\Models\MessagesModel;
        $cm = new \App\Models\ConfigModel;
        $perPage = config('Pager')->perPage;
        $config = $cm->first();

        $user = $um->where('user_id', $user_id)->first();
        if ($user['ban'])
            return $this->replyToChat('⛔️ You are banned by administrator', [
                'parse_mode' => 'html'
            ]);

        // Text commands
        if ($message_text == 'Users' && is_admin($user_id))
        {
            $cm->where('id', 1)->set('page', 1)->update();
            self::show_users($user_id);
        }
        elseif ($message_text == 'Messages' && is_admin($user_id))
        {
            $cm->where('id', 1)->set('page', 1)->update();
            self::show_messages($user_id);
        }
        else
        {
            $mm->save([
                'user_id' => $user_id,
                'message' => $message_text
            ]);
            return $this->replyToChat('✅ Your message has been sent');
        }

        return Request::emptyResponse();
    }

    static function show_users($user_id)
    {
        $um = new \App\Models\UsersModel;
        $perPage = config('Pager')->perPage;
        $config = (new \App\Models\ConfigModel)->first();
        $sl = '';
        $page = $config['page'];
        $l = $um->orderBy('created_at', 'DESC')->paginate($perPage, 'paginate', $page);
        foreach ($l as $i)
        {
            $is_ban = ($i['ban']) ? 'yes' : 'no';
            $sl .= "ID: $i[id]\nUser id: <a href='tg://user?id=$i[user_id]'>$i[user_id]</a>\nBan: $is_ban\nRegistered: $i[created_at]\n\n";
        }
        $reply_markup = new InlineKeyboard([
            [ 'text' => '<', 'callback_data' => 'users_page_prev' ],
            [ 'text' => '>', 'callback_data' => 'users_page_next' ]
        ]);
        $count_pages = ceil($um->countAll() / $perPage);
        Request::sendMessage([
            'chat_id' => $user_id,
            'text' => "List users\n\nPage $page of $count_pages\n\n$sl",
            'reply_markup' => $reply_markup,
            'parse_mode' => 'html'
        ]);
    }

    static function show_messages($user_id)
    {
        $mm = new \App\Models\MessagesModel;
        $perPage = config('Pager')->perPage;
        $config = (new \App\Models\ConfigModel)->first();
        $sl = '';
        $page = $config['page'];
        $l = $mm->orderBy('created_at', 'DESC')->paginate($perPage, 'paginate', $page);
        foreach ($l as $i)
        {
            $sl .= "ID: $i[id]\nUser id: <a href='tg://user?id=$i[user_id]'>$i[user_id]</a>\nMessage: $i[message]\n\n";
        }
        $reply_markup = new InlineKeyboard([
            [ 'text' => '<', 'callback_data' => 'messages_page_prev' ],
            [ 'text' => '>', 'callback_data' => 'messages_page_next' ]
        ]);
        $count_pages = ceil($mm->countAll() / $perPage);
        Request::sendMessage([
            'chat_id' => $user_id,
            'text' => "List messages\n\nPage $page of $count_pages\n\n$sl",
            'reply_markup' => $reply_markup,
            'parse_mode' => 'html'
        ]);
    }
}