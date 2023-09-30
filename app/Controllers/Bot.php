<?php

namespace App\Controllers;

use Longman\TelegramBot\TelegramLog;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Bot extends BaseController
{
    public function hook()
    {
        TelegramLog::initialize(
            new Logger('telegram_bot', [
                (new StreamHandler(ROOTPATH.'writable/logs/tg_debug.log', Logger::DEBUG))->setFormatter(new LineFormatter(null, null, true)),
                (new StreamHandler(ROOTPATH.'writable/logs/tg_error.log', Logger::ERROR))->setFormatter(new LineFormatter(null, null, true))
            ]),
            new Logger('telegram_bot_updates', [
                (new StreamHandler(ROOTPATH.'writable/logs/tg_raw.log', Logger::INFO))->setFormatter(new LineFormatter('%message%' . "\n\n"))
            ])
        );
        //TelegramLog::$always_log_request_and_response = true;

        if (!is_cli())
        {
            // Set the ranges of valid Telegram IPs.
            // https://core.telegram.org/bots/webhooks#the-short-version
            $telegram_ip_ranges = [
                ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'], // literally 149.154.160.0/20
                ['lower' => '91.108.4.0', 'upper' => '91.108.7.255'],       // literally 91.108.4.0/22
            ];
            $ip_dec = (float) sprintf('%u', ip2long($_SERVER['REMOTE_ADDR']));
            $ok = false;
            foreach ($telegram_ip_ranges as $telegram_ip_range) {
                // Make sure the IP is valid.
                $lower_dec = (float) sprintf('%u', ip2long($telegram_ip_range['lower']));
                $upper_dec = (float) sprintf('%u', ip2long($telegram_ip_range['upper']));
                if ($ip_dec >= $lower_dec && $upper_dec >= $ip_dec) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                echo 'Hmm, I don\'t trust you...';
                _log('block access');
                return '';
            }
        }

        try {
            $telegram = new \Longman\TelegramBot\Telegram(getenv('bot_api_key'), getenv('bot_username'));

            $telegram->addCommandsPaths([
                APPPATH.'/BotCommands'
            ]);

            $telegram->useGetUpdatesWithoutDatabase();

            if (is_cli())
                $telegram->handleGetUpdates();
            else
                $telegram->handle();
        }
        catch (\Longman\TelegramBot\Exception\TelegramException $e)
        {
            _log($e->getMessage());
        }
        return '';
    }

    public function set()
    {
        try {
            $telegram = new \Longman\TelegramBot\Telegram(getenv('bot_api_key'), getenv('bot_username'));

            $hook_url = getenv('app.baseURL').'bot_hook';

            $options = [];
            if (getenv('self_signed_certificate') == 1)
                $options['certificate'] = ROOTPATH.'/crt/certificate.crt';

            $result = $telegram->setWebhook($hook_url, $options);

            if ($result->isOk()) {
                echo $result->getDescription();
            }
            else
                echo 'Error';
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            _log($e->getMessage());
        }
    }

    public function unset()
    {
        try {
            $telegram = new \Longman\TelegramBot\Telegram(getenv('bot_api_key'), getenv('bot_username'));

            $result = $telegram->deleteWebhook();

            echo $result->getDescription();
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            _log($e->getMessage());
        }
    }
}