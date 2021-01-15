<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: user_forum-mail_include.php
| Author: riqo89 (riqo89@gmail.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

switch($profile_method) {
    case 'input':
        if (defined('FORUM_EXIST') && defined('FORUM_THREAD_NOTIFY_EXIST')) {
            $options += ['type' => 'radio', 'inline' => TRUE, 'tip' => $locale['uf_thread_notify_desc'], 'options' => [1 => $locale['yes'], 0 => $locale['no']]];
            $user_fields = form_checkbox('user_forum_thread_notify', $locale['uf_thread_notify'], $field_value, $options);
        } elseif (defined('ADMIN_PANEL')) {
            $user_fields = "<div class='alert alert-warning'><i class='fa fa-exclamation-triangle m-r-10'></i> ".$locale['uf_thread_notify_na']."</div>\n";
        }
    break;

 /*   case 'display':
        if (defined('FORUM_EXIST') && defined('FORUM_THREAD_NOTIFY_EXIST')) {
            $user_fields = [
                'title' => $locale['uf_thread_notify'],
                'value' => $field_value ?: ''
            ];
        }
    break;
*/ 
}