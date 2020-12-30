<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/classes/autoloader.php
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
        "PHPFusion\\Calendar\\CalendarServer"           => CALENDAR_CLASS."server.php",
        "PHPFusion\\Calendar\\CalendarAdminModel"       => CALENDAR_CLASS."admin/calendar_admin_model.php",
        "PHPFusion\\Calendar\\CalendarAdminView"        => CALENDAR_CLASS."admin/calendar_admin_view.php",
        "PHPFusion\\Calendar\\CalendarSettingsAdmin"    => CALENDAR_CLASS."admin/controllers/calendar_settings.php",
        "PHPFusion\\Calendar\\CalendarSubmissionsAdmin" => CALENDAR_CLASS."admin/controllers/calendar_submissions.php",
        "PHPFusion\\Calendar\\CalendarSubmissions"      => CALENDAR_CLASS."calendar/calendar_submissions.php",
        "PHPFusion\\Calendar\\CalendarAdmin"            => CALENDAR_CLASS."admin/controllers/calendar.php",
        "PHPFusion\\Calendar\\CalendarView"             => CALENDAR_CLASS."calendar/calendar_view.php",
        "PHPFusion\\Calendar\\Calendar"                 => CALENDAR_CLASS."calendar/calendar.php",
        "PHPFusion\\Calendar\\Functions"                => CALENDAR_CLASS."functions.php",
        "PHPFusion\\httpdownload"                       => INCLUDES."class.httpdownload.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});
