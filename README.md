# phpbb31-newsrobot

This hack requires the Converter by @dedalozzo.

https://github.com/dedalozzo/converter

## Hack some phpBB code?

Yes, unfortunally.

You need to replace some actual PHP code within phpBB, one file. includes/functions_posting.php:

Replace the following (only one occurance in the file):

```php
$current_time = time();
```

with

```php
if(!$data['post_time']) {
     $current_time = time();
} else {
     $current_time = $data['post_time'];
}
```

... and your actually phpBB file hacking should be done. REMEMBER THIS BIT THOUGH. Once you update your phpBB to a new version, you'll most likely have to redo this part or your script will not be altering the dates of the post to the one of the RRS feed.

## You also need a special database for this.

This bot needs to be able to keep track of what RSS posts it has already
tossed into your board - or you will end up with unlimited amount of dupes.

This assumes your phpBB database is named phpbb. If it's not, update phpbb.phpbb_topics below (and in the newsbot php file) to your database name. This database step is necessary to stay compatible with phpBB SEO. If you don't care, you can remove those bits from the bot.

```mysql
CREATE DATABASE newsbot;
CONNECT newsbot;
CREATE TABLE posts ( id int NOT NULL AUTO_INCREMENT PRIMARY KEY, guid varchar(255), pubdate varchar(32), link varchar(255), title varchar(255) );
GRANT all ON phpbb.phpbb_topics TO 'newsbot'@'localhost' IDENTIFIED BY 'password';
GRANT all ON newsbot.* TO 'newsbot'@'localhost' IDENTIFIED BY 'password';
```


# More information?

Join the discussion on my blog at https://www.engren.se/2015/09/21/phpbb-3-1-news-robot/ if you have any feedback! :-)

