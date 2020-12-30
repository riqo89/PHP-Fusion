<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss/classes/autoloader.php
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
require_once INCLUDES."infusions_include.php";

spl_autoload_register(function ($className) {
    $autoload_register_paths = [
        "PHPFusion\\RSS\\RssServer"           => RSS_CLASS."server.php",
        "PHPFusion\\RSS\\RssAdminModel"       => RSS_CLASS."admin/rss_admin_model.php",
        "PHPFusion\\RSS\\RssAdminView"        => RSS_CLASS."admin/rss_admin_view.php",
        "PHPFusion\\RSS\\RssSettingsAdmin"    => RSS_CLASS."admin/controllers/rss_settings.php",
        "PHPFusion\\RSS\\RssSubmissionsAdmin" => RSS_CLASS."admin/controllers/rss_submissions.php",
        "PHPFusion\\RSS\\RssSubmissions"      => RSS_CLASS."rss/rss_submissions.php",
        "PHPFusion\\RSS\\RssAdmin"            => RSS_CLASS."admin/controllers/rss.php",
        "PHPFusion\\RSS\\RssView"             => RSS_CLASS."rss/rss_view.php",
        "PHPFusion\\RSS\\Rss"                 => RSS_CLASS."rss/rss.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});
