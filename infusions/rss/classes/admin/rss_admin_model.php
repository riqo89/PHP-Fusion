<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss/classes/admin/rss_admin_model.php
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
namespace PHPFusion\RSS;

class RssAdminModel extends RssServer {
    private static $admin_locale = [];
    protected $default_data = [
        'rss_id'         => 0,
        'rss_cat_id'     => 0,
        'rss_title'   => '',
        'rss_content'     => '',
        'rss_datestamp'  => TIME,
        'rss_name'       => 0,
        'rss_link'     => '',
        'rss_visibility' => 0,
        'rss_status'     => 1,
        'rss_language'   => LANGUAGE
    ];

    public function __construct() {
        parent::__construct();

        self::$rss_settings = get_settings("rss");
    }

    public static function get_rssAdminLocale() {
        if (empty(self::$admin_locale)) {
            $admin_locale_path = LOCALE.'English/admin/settings.php';
            if (file_exists(LOCALE.LOCALESET.'admin/settings.php')) {
                $admin_locale_path = LOCALE.LOCALESET.'admin/settings.php';
            }
            $locale = fusion_get_locale('', [RSS_LOCALE, $admin_locale_path]);
            self::$admin_locale = $locale;
        }

        return self::$admin_locale;
    }
}
