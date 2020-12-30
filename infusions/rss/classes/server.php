<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss/classes/server.php
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

class RssServer {
    protected static $rss_settings = [
        'rss_allow_submission' => 0,
        'rss_cronjob_refresh'  => 1800,
        'rss_cronjob_include'  => 1
    ];
    private static $rss_instance = NULL;
    private static $rss_submit_instance = NULL;
    private static $rss_admin_instance = NULL;
    public $save;
    public $rss_allow_submission;
    public $rss_cronjob_refresh;
    public $rss_cronjob_include;
    public $catid;

    public function __construct() {
        self::$rss_settings = get_settings("rss");
        self::globinf();
    }

    public function globinf() {

        $this->save = (string)filter_input(INPUT_POST, 'savesettings', FILTER_DEFAULT);
        $this->rss_allow_submission = filter_input(INPUT_POST, 'rss_allow_submission', FILTER_DEFAULT);
        $this->rss_cronjob_refresh = filter_input(INPUT_POST, 'rss_cronjob_refresh', FILTER_DEFAULT);
        $this->rss_cronjob_include = filter_input(INPUT_POST, 'rss_cronjob_include', FILTER_DEFAULT);
        $this->catid = isset($_GET['cat_id']) ? $_GET['cat_id'] : 0;
    }

    public static function Rss() {
        if (self::$rss_instance === NULL) {
            self::$rss_instance = new RssView();
        }

        return self::$rss_instance;
    }

    public static function RssSubmit() {
        if (self::$rss_submit_instance === NULL) {
            self::$rss_submit_instance = new RssSubmissions();
        }

        return self::$rss_submit_instance;
    }

    public static function RssAdmin() {
        if (self::$rss_admin_instance === NULL) {
            self::$rss_admin_instance = new RssAdminView();
        }

        return self::$rss_admin_instance;
    }
}
