<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss/infusion_db.php
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

// Locales
if (!defined("RSS_LOCALE")) {
    if (file_exists(INFUSIONS."rss/locale/".LOCALESET."rss.php")) {
        define("RSS_LOCALE", INFUSIONS."rss/locale/".LOCALESET."rss.php");
    } else {
        define("RSS_LOCALE", INFUSIONS."rss/locale/English/rss.php");
    }
}

// Paths
if (!defined('RSS_CLASS')) {
    define('RSS_CLASS', INFUSIONS.'rss/classes/');
}
// Database
if (!defined('DB_RSS')) {
    define('DB_RSS', DB_PREFIX.'rss');
}
if (!defined('DB_RSS_CATS')) {
    define('DB_RSS_CATS', DB_PREFIX.'rss_cats');
}

// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons("RS", "<i class='admin-ico fa fa-fw fa-life-buoy'></i>");

$inf_settings = get_settings('rss');
if (!empty($inf_settings['rss_allow_submission']) && $inf_settings['rss_allow_submission']) {
    \PHPFusion\Admins::getInstance()->setSubmitData('r', [
        'infusion_name' => 'rss',
        'link'          => INFUSIONS."rss/rss_submit.php",
        'submit_link'   => "submit.php?stype=r",
        'submit_locale' => fusion_get_locale('RS', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('rss_submit', RSS_LOCALE),
        'admin_link'    => INFUSIONS."rss/rss_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
    ]);
}

// Cronjob
if(get_settings("rss", "rss_cronjob_include") == 1)  require_once "cron.php";
