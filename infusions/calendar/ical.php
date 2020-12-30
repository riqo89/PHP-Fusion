<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/ical.php
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
require_once __DIR__.'/../../maincore.php';
if (!defined('CALENDAR_EXIST')) die();
require_once CALENDAR_CLASS.'autoloader.php';

define('ICAL_FORMAT', '%Y%m%dT%H%M%S');
define('LINE_BREAK', PHP_EOL);

if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) && !empty($_GET['cat_id'])) {
    $filters = [
        'where'     => 'ce.calendar_cat_id = '.intval($_GET['cat_id'])
    ];  
} elseif (isset($_GET['event_id']) && isnum($_GET['event_id']) && !empty($_GET['event_id'])) {
    $filters = [
        'where'     => 'ce.event_id = '.intval($_GET['event_id'])
    ];
} else {
    $filters = [];
}

//Get calendar info
$info = \PHPFusion\Calendar\CalendarServer::Calendar()->get_CalendarData($filters);

//Get calendar cats / names
foreach($info['calendar_categories'] as $cats) {
    $name[] = $cats['calendar_cat_name'];
}

$ical_object[] = 'BEGIN:VCALENDAR';
$ical_object[] = 'VERSION:2.0';
$ical_object[] = 'METHOD:PUBLISH';
$ical_object[] = 'NAME:'.fusion_get_settings('sitename').': '.implode(', ', $name);
$ical_object[] = 'X-WR-CALNAME:'.fusion_get_settings('sitename').': '.implode(', ', $name);
$ical_object[] = 'PRODID:-//'.fusion_get_settings('sitename').'//'.fusion_get_settings('siteusername').'//'.strtoupper(fusion_get_locale('short_lang_name'));

//Get all events
foreach($info['calendar_items'] as $event) {
    $ical_object[] = 'BEGIN:VEVENT';
    $ical_object[] = 'CLASS:PUBLIC';
    $ical_object[] = 'DTSTART:'.showdate(ICAL_FORMAT, $event['event_start']);
    $ical_object[] = 'DTEND:'.showdate(ICAL_FORMAT, $event['event_end']);
    $ical_object[] = 'DTSTAMP:'.showdate(ICAL_FORMAT, $event['event_datestamp']);
    $ical_object[] = 'SUMMARY:'.$event['event_title'];
    $ical_object[] = 'DESCRIPTION:'.$event['event_description'];
    $ical_object[] = 'X-ALT-DESC;FMTTYPE=text/html:'.$event['event_description'];
    $ical_object[] = 'UID:'.$event['event_id'].'@'.fusion_get_settings('site_host');
    $ical_object[] = 'ORGANIZER;CN='.$event['user_name'].':MAILTO:'.$event['user_email'];
    //$ical_object[] = 'LAST-MODIFIED:'.showdate(ICAL_FORMAT, $event['event_datestamp']);
    $ical_object[] = 'LOCATION:'.$event['event_location'];
    $ical_object[] = 'URL;VALUE=URI:'.fusion_get_settings('siteurl').'infusions/calendar/calendar.php?view=list&event_id='.$event['event_id'];
    $ical_object[] = 'END:VEVENT';
}

$ical_object[] = 'END:VCALENDAR';

if (isset($_GET['event_id']) && isnum($_GET['event_id']) && !empty($_GET['event_id'])) {
    $filename = stripfilename(fusion_get_settings('sitename').'_'.$event['event_title']).'.ics';
} elseif (isset($_GET['cat_id']) && isnum($_GET['cat_id']) && !empty($_GET['cat_id'])) {
    $filename = stripfilename(fusion_get_settings('sitename').'_'.$info['calendar_categories'][intval($_GET['cat_id'])]['calendar_cat_name']).'.ics';
} else {
    $filename = stripfilename(fusion_get_settings('sitename').'_'.implode('_', $name)).'.ics';
}

// Set the headers
header('Content-type: text/calendar; charset='.strtolower(fusion_get_locale('charset')));
header('Content-Disposition: attachment; filename="'.$filename.'"');

echo implode(PHP_EOL, $ical_object);