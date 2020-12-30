<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: rss_rewrite_include.php
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
    "%rss_cat_id%"   => "([0-9]+)",
    "%rss_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%type%"         => "(RS)",
    "%stype%"        => "(r)"
];

$pattern = [
    "print/%type%/%cat_id%/%rss_cat_name%"             => "print.php?type=%type%&amp;item_id=%cat_id%",
    "submit-%stype%/rss"                               => "submit.php?stype=%stype%",
    "submit-%stype%/rss/submitted-and-thank-you"       => "submit.php?stype=%stype%&amp;submitted=q",
    "rss"                                              => "infusions/rss/rss.php",
    "rss/category/%rss_cat_id%"                        => "infusions/rss/rss.php?cat_id=%rss_cat_id%"
];

$pattern_tables["%rss_cat_id%"] = [
    "table"       => DB_RSS_CATS,
    "primary_key" => "rss_cat_id",
    "id"          => ["%rss_cat_id%" => "rss_cat_id"],
    "columns"     => []
];
