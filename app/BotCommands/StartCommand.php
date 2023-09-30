<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Keyboard;

class StartCommand extends SystemCommand
{
    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $private_only = true;

    public function execute(): ServerResponse
    {
        $deep_linking_parameter = $this->getMessage()->getText(true);

        $message = $this->getMessage();
        $user_id = $message->getFrom()->getId();
        $first_name = $message->getFrom()->getFirstName();
        $last_name = $message->getFrom()->getLastName();

        $m = new \App\Models\UsersModel;
        $u = $m->where('user_id', $user_id)->first();
        if (is_null($u))
        {
            $m->save([
                'user_id' => $user_id
            ]);
        }

        $reply_markup = NULL;
        if (is_admin($user_id))
        {
            $reply_markup = (new Keyboard(
                [ 'Users', 'Messages' ]
            ))->setResizeKeyboard(true);
        }

        return $this->replyToChat("😃 Welcome, $first_name!\n📝 Write your message below and we will respond as quickly as possible", [
            'parse_mode' => 'html',
            'reply_markup' => $reply_markup
        ]);
    }
}