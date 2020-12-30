<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/classes/admin/calendar_admin_model.php
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
namespace PHPFusion\Calendar;

class CalendarAdminModel extends CalendarServer {
    private static $admin_locale = [];
    protected $default_data = [
        'event_id'              => 0,
        'calendar_cat_id'       => 0,
        'event_title'           => '',
        'event_description'     => '',
        'event_location'        => '',
        'event_datestamp'       => TIME,
        'event_start'           => TIME,
        'event_end'             => TIME + 3600,
        'event_name'            => 0,
        'event_breaks'          => 'n',
        'event_visibility'      => 0,
        'event_status'          => 1,
        'event_participation'   => 1,
        'event_language'        => LANGUAGE
    ];

    public function __construct() {
        parent::__construct();

        self::$calendar_settings = get_settings("calendar");
    }

    public static function get_calendarAdminLocale() {
        if (empty(self::$admin_locale)) {
            $admin_locale_path = LOCALE.'English/admin/settings.php';
            if (file_exists(LOCALE.LOCALESET.'admin/settings.php')) {
                $admin_locale_path = LOCALE.LOCALESET.'admin/settings.php';
            }
            $locale = fusion_get_locale('', [CALENDAR_LOCALE, $admin_locale_path]);
            self::$admin_locale = $locale;
        }

        return self::$admin_locale;
    }
}
