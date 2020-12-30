<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss_panel.php
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

require_once RSS_CLASS.'autoloader.php';
require_once INFUSIONS.'rss/templates.php';

$all_items =[];

//Reorder function
function reorder($key) {
    return function ($a, $b) use ($key) {
        return strnatcmp($b[$key], $a[$key]);
    };
}

//Merge all feed items in one array
$info = \PHPFusion\RSS\RssServer::Rss()->get_RssData();
foreach($info['rss_categories'] as $key => $cats) {
    $rss = \PHPFusion\RSS\RssServer::Rss()->set_RssInfo($cats['rss_cat_id']);
    foreach($rss['rss_items'] as $feed) {
        foreach(\Defender::decode($feed['rss_content']) as $item) $all_items[] = $item;
    }

}

//Sort by Date, latest
usort($all_items, reorder('pubDate'));

openside($locale['rss_0000']);
    echo render_rss_content($all_items, [
        'show_enclosure' => FALSE,
        'show_pubDate' => FALSE,
        'show_link_host' => TRUE,
        'max_items' => 10,
        'max_desc_length' => 100,
        'is_decoded' => TRUE
        ]);

    echo "<em><a href='".INFUSIONS."rss/rss.php'>".$locale['rss_0260']."</a></em>";
closeside();


