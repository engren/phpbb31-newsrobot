<?php

/*
 *
 * News Robot Poster - hans@ENGREN.SE
 * 
 * Requires some patches tointernal phpBB files to be able to handle
 * timestamp alterations.
 *
 * Requires: https://github.com/dedalozzo/converter/tree/master/src/Converter
 *
 */

require_once("class/Converter.php");
require_once("class/HTMLConverter.php");

define('IN_PHPBB', true);
$phpbb_root_path = '/var/www/forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

include($phpbb_root_path . 'config.' . $phpEx);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

$user->session_begin();
$user->session_kill(false);
$user->session_create(53);
$auth->acl($user->data);

$feed = new DOMDocument();
$feed->load('https://www.bitcoin.com/feed/');
$json = array();
$json['title'] = $feed->getElementsByTagName('channel')->item(0)->getElementsByTagName('title')->item(0)->firstChild->nodeValue;
$json['description'] = $feed->getElementsByTagName('channel')->item(0)->getElementsByTagName('description')->item(0)->firstChild->nodeValue;
$json['link'] = $feed->getElementsByTagName('channel')->item(0)->getElementsByTagName('link')->item(0)->firstChild->nodeValue;
$items = $feed->getElementsByTagName('channel')->item(0)->getElementsByTagName('item');

$json['item'] = array();
$i = 0;

foreach($items as $key => $item) {
        $title = $item->getElementsByTagName('title')->item(0)->firstChild->nodeValue;
        $description = $item->getElementsByTagName('description')->item(0)->firstChild->nodeValue;
        $content = $item->getElementsByTagName('encoded')->item(0)->firstChild->nodeValue;
        $pubDate = $item->getElementsByTagName('pubDate')->item(0)->firstChild->nodeValue;
        $guid = $item->getElementsByTagName('guid')->item(0)->firstChild->nodeValue;
        $weblink = $item->getElementsByTagName('link')->item(0)->firstChild->nodeValue;

        $json['item'][$key]['title'] = $title;
        $json['item'][$key]['description'] = $description;
        $json['item'][$key]['content:encoded'] = $content;
        $json['item'][$key]['pubdate'] = $pubDate;
        $json['item'][$key]['guid'] = $guid;
        $json['link'][$key]['link'] = $weblink;

        $link = mysql_connect('localhost', 'newsbot', 'password');
        if (!$link) {
                die('Could not connect: ' . mysql_error());
        }

        mysql_select_db("newsbot", $link);
        $actualtime=strtotime("$pubDate");
        $current_time=$actualtime;

        $result = mysql_query("SELECT * FROM posts where guid='$guid'", $link);
        $num_rows = mysql_num_rows($result);

        if($num_rows == 0) {
                unset($array);
                unset($output);

                $array = explode("\n",$description);
                foreach($array as $arr) {
                        if(!(preg_match("/The\spost/",$arr))) {
                                $output[] = $arr;
                        }
                }

                $out = implode("", $output);
                $out .= "... \n<br/><br/><a href=\"$weblink\">Read the full article on bitcoin.com here.</a>";
                $converter = new Converter\HTMLConverter($out, $id);
                $bbcontent = $converter->toBBCode();

                $title="[bitcoin] $title";
                $my_subject   = utf8_normalize_nfc($title);
                $my_text   = utf8_normalize_nfc($bbcontent);

                $poll = $uid = $bitfield = $options = '';
                generate_text_for_storage($title, $uid, $bitfield, $options, false, false,false);
                generate_text_for_storage($bbcontent, $uid, $bitfield, $options, true, true, true);

                $data = array(
                        'forum_id'      => 29,
                        'icon_id'      => false,

                        'enable_bbcode'      => true,
                        'enable_smilies'   => true,
                        'enable_urls'      => true,
                        'enable_sig'      => true,

                        'message'      => $bbcontent,
                        'message_md5'   => md5($bbcontent),

                        'bbcode_bitfield'   => $bitfield,
                        'bbcode_uid'      => $uid,

                        'post_edit_locked'   => 0,
                        'topic_title'      => $title,
                        'notify_set'      => false,
                        'notify'         => false,
                        'post_time'       => $actualtime,
                        'topic_url'       => false,
                        'enable_indexing'   => true,
                );

                echo $bbcontent;

                submit_post('post', $my_subject, 'News Robot', POST_NORMAL, $poll, $data);
                $newtopic=$data['topic_id'];

                echo "New topic: $newtopic ($guid)\n";

                $title = str_replace("'", "\'", $title);

                mysql_query("update phpbb.phpbb_topics set topic_url='' where topic_id='$newtopic'", $link);
                mysql_query("insert into newsbot.posts values(NULL,'$guid','$pubDate','$weblink','$title')", $link);

        }
        mysql_close($link);
}

?>
