<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: forum_thread_notify/infusion.php
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
defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', FORUM_THREAD_NOTIFY_LOCALE);

// Infusion general information
$inf_title = $locale['thread_notify_0000'];
$inf_description = $locale['thread_notify_0001'];
$inf_version = '1.0';
$inf_developer = 'riqo';
$inf_email = 'dev@corico.cloud';
$inf_weburl = 'https://github.com/riqo89/PHP-Fusion';
$inf_folder = 'forum_thread_notify';
$inf_image = 'forum_thread_notify.svg';


$inf_insertdbrow[] = DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'THREAD', 'html', 0, '".$locale['thread_notify_0010']."', '".$locale['thread_notify_0011']."', '".$locale['thread_notify_0012']."', '', '', '".LANGUAGE."')";
$inf_deldbrow[] = DB_EMAIL_TEMPLATES." WHERE template_key='THREAD'";

