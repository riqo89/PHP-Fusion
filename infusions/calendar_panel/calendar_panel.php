<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar_panel/calendar_panel.php
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

require_once CALENDAR_CLASS.'autoloader.php';
require_once CALENDAR.'templates.php';

add_to_head("<link rel='stylesheet' type='text/css' href='".CALENDAR_CSS."calendar.css'>");

$filters = [
    'where'     => '(ce.event_start >= '.TIME.' OR ce.event_end >= '.TIME.')',
    'orderby'   => 'ce.event_start ASC',
    'limit'     => 5
]; 

openside(fusion_get_locale('calendar_0332'));
echo events_list($filters);
closeside();


