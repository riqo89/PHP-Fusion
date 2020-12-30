<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss/rss.php
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
require_once __DIR__.'/../../maincore.php';

/**
 * Cron Job (RSS-Feeds)
 */
if (defined('RSS_EXIST')) {
    if ((isset($_GET['force_cron']) && isNum($_GET['force_cron']) && $_GET['force_cron'] == 1) || get_settings("rss", "rss_cronjob_hour") < (TIME - get_settings("rss", "rss_cronjob_refresh"))) {
        require_once RSS_CLASS.'autoloader.php';
        \PHPFusion\RSS\RssServer::Rss()->refresh_RssFeeds();  

        $inputSettings = [
            'settings_name'  => 'rss_cronjob_hour',
            'settings_value' => TIME,
            'settings_inf'   => 'rss',
        ];

        dbquery_insert(DB_SETTINGS_INF, $inputSettings, 'update', ['primary_key' => 'settings_name']);
    }
}
