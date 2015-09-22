# phpbb31-newsrobot

This hack requires the Converter by @dedalozzo.

https://github.com/dedalozzo/converter

## Hack some phpBB code?

Yes, unfortunally.

You need to replace some actual PHP code within phpBB, one file. includes/functions_posting.php:

Replace the following (only one occurance in the file):

$current_time = time();
with

if(!$data['post_time']) {
     $current_time = time();
} else {
     $current_time = $data['post_time'];
}
... and your actually phpBB file hacking should be done. REMEMBER THIS BIT THOUGH. Once you update your phpBB to a new version, you'll most likely have to redo this part or your script will not be altering the dates of the post to the one of the RRS feed.

# More information?

Join the discussion on my blog at https://www.engren.se/2015/09/21/phpbb-3-1-news-robot/ if you have any feedback! :-)

