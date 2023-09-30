<?php

function _log($text, $file = 'error')
{
    $ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : 'cli';
    file_put_contents(ROOTPATH."writable/logs/${file}.log", '['.date('d/m/Y H:i:s').' '.$ip."]\n".$text."\n\n", FILE_APPEND);
}

function is_admin($user_id)
{
    return (getenv('bot_admin_user_id') == $user_id);
}