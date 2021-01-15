<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: forum_thread_notify.php
| Author: riqo (dev@corico.cloud)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

self::$locale += fusion_get_locale("", FORUM_THREAD_NOTIFY_LOCALE);

$thread_data = dbarray(dbquery("SELECT ft.thread_id, ft.forum_id, ft.thread_lastpostid, ft.thread_postcount, ft.thread_subject, tu.user_id, tu.user_name
                                FROM ".DB_FORUM_THREADS." ft
                                LEFT JOIN ".DB_USERS." tu ON ft.thread_author=tu.user_id
                                WHERE thread_id=:thread_id", [':thread_id' => intval($_GET['thread_id'])]));

$thread_data['thread_link'] = fusion_get_settings('siteurl')."infusions/forum/viewthread.php?thread_id=".$thread_data['thread_id']."&pid=".$thread_data['thread_lastpostid']."#post_".$thread_data['thread_lastpostid'];

$notify_result = dbquery("SELECT user_id, user_name, user_email, user_forum_thread_notify
                          FROM ".DB_USERS." WHERE user_forum_thread_notify=1 AND user_id !=:my_id", [':my_id' => fusion_get_userdata('user_id')]);

if (dbrows($notify_result)) {
    $forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
    require_once INCLUDES.'sendmail_include.php';

    $template_result = dbquery("SELECT template_key, template_active FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='THREAD' LIMIT 1");
    if (dbrows($template_result) > 0) {
        $template_data = dbarray($template_result);
        if ($template_data['template_active'] == 1) {
            while ($data = dbarray($notify_result)) {
                if ($this->check_forum_access($forum_index, '', intval($_GET['thread_id']), $data['user_id'])) {
                    sendemail_template("THREAD", $thread_data['thread_subject'], "", $thread_data['user_name'], $data['user_name'], $thread_data['thread_link'], $data['user_email']);
                }
            }
        } else {
            while ($data = dbarray($notify_result)) {
                if ($this->check_forum_access($forum_index, '', intval($_GET['thread_id']), $data['user_id'])) {
                    $message_subject = str_replace("{SITENAME}", self::$settings['sitename'], self::$locale['thread_notify_0013']);
                    $message_content = strtr(self::$locale['thread_notify_0014'], [
                        '{USERNAME}'       => $data['user_name'],
                        '{THREAD_SUBJECT}' => $thread_data['thread_subject'],
                        '{THREAD_AUTHOR}'  => $thread_data['user_name'],
                        '{THREAD_URL}'     => $thread_data['thread_link'],
                        '{SITENAME}'       => self::$settings['sitename'],
                        '{SITEUSERNAME}'   => self::$settings['siteusername'],
                    ]);
                    sendemail($data['user_name'], $data['user_email'], self::$settings['siteusername'], self::$settings['siteemail'], $message_subject, $message_content);
                }
            }
        }
    } else {
        while ($data = dbarray($notify_result)) {
            if ($this->check_forum_access($forum_index, '', intval($_GET['thread_id']), $data['user_id'])) {
                $message_subject = str_replace("{SITENAME}", self::$settings['sitename'], self::$locale['thread_notify_013']);
                $message_content = strtr(self::$locale['thread_notify_0014'], [
                    '{USERNAME}'       => $data['user_name'],
                    '{THREAD_SUBJECT}' => $thread_data['thread_subject'],
                    '{THREAD_AUTHOR}'  => $thread_data['user_name'],
                    '{THREAD_URL}'     => $thread_data['thread_link'],
                    '{SITENAME}'       => self::$settings['sitename'],
                    '{SITEUSERNAME}'   => self::$settings['siteusername'],
                ]);
                sendemail($data['user_name'], $data['user_email'], self::$settings['siteusername'], self::$settings['siteemail'], $message_subject, $message_content);
            }
        }
    }
}