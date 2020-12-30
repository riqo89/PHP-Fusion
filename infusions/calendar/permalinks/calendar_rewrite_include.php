<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: calendar_rewrite_include.php
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

$regex = [
    "%event_id%"                => "([0-9]+)",
    "%event_title%"             => "([0-9a-zA-Z._\W]+)",
    "%calendar_cat_id%"         => "([0-9]+)",
    "%calendar_cat_name%"       => "([0-9a-zA-Z._\W]+)",
    "%type%"                    => "(CA)",
    "%stype%"                   => "(c)",
    "%view"                     => "((list|archive|cats))",
    "%day%"                     => "(\d{4}-\d{2}-\d{2})",
    "%month%"                   => "(\d{4}-\d{2})",
    "%year%"                    => "(\d{4})"
];

$pattern = [
    "submit-%stype%/calendar"                                               => "submit.php?stype=%stype%",
    "submit-%stype%/calendar/submitted-and-thank-you"                       => "submit.php?stype=%stype%&amp;submitted=c",
    "calendar"                                                              => "infusions/calendar/calendar.php",
    "calendar/day/%day%"                                                    => "infusions/calendar/calendar.php?day=%day%",
    "calendar/month/%month%"                                                => "infusions/calendar/calendar.php?month=%month%",
    "calendar/year/%year%"                                                  => "infusions/calendar/calendar.php?year=%year%",
    "calendar/category/%calendar_cat_id%/%calendar_cat_name%"               => "infusions/calendar/calendar.php?cat_id=%calendar_cat_id%",
    "calendar/category/%calendar_cat_id%/day/%day%"                         => "infusions/calendar/calendar.php?day=%day%&cat_id=%calendar_cat_id%",
    "calendar/category/%calendar_cat_id%/month/%month%"                     => "infusions/calendar/calendar.php?month=%month%&cat_id=%calendar_cat_id%",
    "calendar/category/%calendar_cat_id%/year/%year%"                       => "infusions/calendar/calendar.php?year=%year%&cat_id=%calendar_cat_id%",
    "calendar/view/%view%"                                                  => "infusions/calendar/calendar.php?view=%view%",
    "calendar/view/%view%/event/%event_id%/%event_title%"                   => "infusions/calendar/calendar.php?view=%view%&event_id=%event_id%",
    "calendar/view/%view%/month/%month%"                                    => "infusions/calendar/calendar.php?view=%view%&month=%month%",
    "calendar/view/%view%/category/%calendar_cat_id%/%calendar_cat_name%"   => "infusions/calendar/calendar.php?view=%view%&cat_id=%calendar_cat_id%",
    "calendar/view/%view%/category/%calendar_cat_id%/month/%month%"         => "infusions/calendar/calendar.php?view=%view%&month=%month%&cat_id=%calendar_cat_id%"
];

$pattern_tables["%event_id%"] = [
    "table"       => DB_CALENDAR_EVENTS,
    "primary_key" => "event_id",
    "id"          => ["%event_id%" => "event_id"],
    "columns"     => ["%event_title%" => "event_title"]
];

$pattern_tables["%calendar_cat_id%"] = [
    "table"       => DB_CALENDAR_CATS,
    "primary_key" => "calendar_cat_id",
    "id"          => ["%calendar_cat_id%" => "calendar_cat_id"],
    "columns"     => ["%calendar_cat_name%" => "calendar_cat_name"]
];
