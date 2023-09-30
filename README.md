Telegram bot for feedback!

## How to install it

Downloading zip or use git:
```
git clone https://github.com/iyamk/telegram-bot-feedback.git
```

## How to configure this nonsense?

Edit these lines in env file:
```
CI_ENVIRONMENT = production
bot_api_key = '<YOUR BOT KEY>'
bot_username = '<YOUR BOT USERNAME>'
bot_admin_user_id = '<YOUR USER ID IN TELEGRAM>'
app.baseURL = '<PLEASE INDICATE THE LINK TO THE SITE IF YOU ARE USING HOSTING>'
```
Rename the **env** file to **.env**

Run **longpool.sh** and I think hosting for such a bot is not really needed

## About the bot

This bot, as an experiment, turned out to be excrement, because it is not convenient as in python and it would be possible to do without the php-telegram-bot library, which in turn is written poorly and it is impossible to write powerful bots on it for $1000
