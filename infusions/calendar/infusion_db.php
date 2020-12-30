<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/infusion_db.php
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

// Locales
if (!defined("CALENDAR_LOCALE")) {
    if (file_exists(INFUSIONS."calendar/locale/".LOCALESET."calendar.php")) {
        define("CALENDAR_LOCALE", INFUSIONS."calendar/locale/".LOCALESET."calendar.php");
    } else {
        define("CALENDAR_LOCALE", INFUSIONS."calendar/locale/English/calendar.php");
    }
}

// Paths
if (!defined('CALENDAR')) {
    define('CALENDAR', INFUSIONS.'calendar/');
}

if (!defined('CALENDAR_CLASS')) {
    define('CALENDAR_CLASS', INFUSIONS.'calendar/classes/');
}

if (!defined('CALENDAR_ATTACHMENTS')) {
    define('CALENDAR_ATTACHMENTS', INFUSIONS.'calendar/attachments/');
}

if (!defined('CALENDAR_SUBMISSIONS')) {
    define('CALENDAR_SUBMISSIONS', INFUSIONS.'calendar/submissions/');
}

if (!defined('CALENDAR_TEMPLATES')) {
    define('CALENDAR_TEMPLATES', INFUSIONS.'calendar/templates/');
}

if (!defined('CALENDAR_CSS')) {
    define('CALENDAR_CSS', INFUSIONS.'calendar/templates/css/');
}

// Database
if (!defined('DB_CALENDAR_EVENTS')) {
    define('DB_CALENDAR_EVENTS', DB_PREFIX.'calendar_events');
}
if (!defined('DB_CALENDAR_CATS')) {
    define('DB_CALENDAR_CATS', DB_PREFIX.'calendar_cats');
}
if (!defined('DB_CALENDAR_PARTICIPATION')) {
    define('DB_CALENDAR_PARTICIPATION', DB_PREFIX.'calendar_participation');
}

// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons("CA", "<i class='admin-ico fa fa-fw fa-life-buoy'></i>");

$inf_settings = get_settings('calendar');
if (!empty($inf_settings['calendar_allow_submission']) && $inf_settings['calendar_allow_submission']) {
    \PHPFusion\Admins::getInstance()->setSubmitData('c', [
        'infusion_name' => 'calendar',
        'link'          => INFUSIONS."calendar/calendar_submit.php",
        'submit_link'   => "submit.php?stype=c",
        'submit_locale' => fusion_get_locale('CA', LOCALE.LOCALESET."admin/main.php"),
        'title'         => fusion_get_locale('calendar_submit', CALENDAR_LOCALE),
        'admin_link'    => INFUSIONS."calendar/calendar_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s"
    ]);
}

\PHPFusion\Admins::getInstance()->setFolderPermissions('calendar', [
    'infusions/calendar/attachments/'         => TRUE,
    'infusions/calendar/submissions/'         => TRUE
]);