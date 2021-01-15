<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: forum/infusion_db.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

if (!defined("FORUM_THREAD_NOTIFY_INCLUDES")) {
    define("FORUM_THREAD_NOTIFY_INCLUDES", INFUSIONS."forum_thread_notify/includes/");
}

if (!defined("FORUM_THREAD_NOTIFY_LOCALE")) {
    if (file_exists(INFUSIONS."forum_thread_notify/locale/".LOCALESET."/forum_thread_notify.php")) {
        define("FORUM_THREAD_NOTIFY_LOCALE", INFUSIONS."forum_thread_notify/locale/".LOCALESET."/forum_thread_notify.php");
    } else {
        define("FORUM_THREAD_NOTIFY_LOCALE", INFUSIONS."forum_thread_notify/locale/English/forum_thread_notify.php");
    }
}