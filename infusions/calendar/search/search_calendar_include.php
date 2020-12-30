<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: search_calendar_include.php
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
namespace PHPFusion\Search;
require_once CALENDAR_CLASS.'functions.php';

use PHPFusion\ImageRepo;
use PHPFusion\Search;
use PHPFusion\Calendar\Functions;

defined('IN_FUSION') || exit;

if (defined('CALENDAR_EXIST')) {
    $formatted_result = '';
    $locale = fusion_get_locale('', CALENDAR."locale/".LOCALESET."search/calendar.php");
    $item_count = "0 ".$locale['ca402']." ".$locale['522']."<br />\n";
    $date_search = (Search_Engine::get_param('datelimit') != 0 ? ' AND event_start >= '.(TIME - Search_Engine::get_param('datelimit')) : '');

    if (Search_Engine::get_param('stype') == 'calendar' || Search_Engine::get_param('stype') == 'all') {
        
        $sort_by = [
            'datestamp' => "event_start",
            'subject'   => "event_title",
            'author'    => "event_name",
        ];
        
        $order_by = [
            '0' => ' DESC',
            '1' => ' ASC',
        ];

        $sortby = !empty(Search_Engine::get_param('sort')) ? " ORDER BY ".$sort_by[Search_Engine::get_param('sort')].$order_by[Search_Engine::get_param('order')] : '';
         
        switch (Search_Engine::get_param('fields')) {
            case 2:
                Search_Engine::search_column('event_title', 'calendar');
                Search_Engine::search_column('event_description', 'calendar');
                Search_Engine::search_column('event_name', 'calendar');
                break;
            case 1:
                Search_Engine::search_column('event_description', 'calendar');
                break;
            default:
                Search_Engine::search_column('event_title', 'calendar');
        }

        if (!empty(Search_Engine::get_param('search_param'))) {
            $query = "SELECT te.*, tec.*
                FROM ".DB_CALENDAR_EVENTS." te
                INNER JOIN ".DB_CALENDAR_CATS." tec ON te.calendar_cat_id=tec.calendar_cat_id
                ".(multilang_table("CA") ? "WHERE ".in_group('te.event_language', LANGUAGE)." AND " : "WHERE ")
                .groupaccess('te.event_visibility')." AND ".Search_Engine::search_conditions('calendar').$date_search;
            $result = dbquery($query, Search_Engine::get_param('search_param'));
            $rows = dbrows($result);
        } else {
            $rows = 0;
        }

        if ($rows != 0) {
            
            $item_count = "<a href='".BASEDIR."search.php?stype=calendar&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['ca401'] : $locale['ca402'])." ".$locale['522']."</a><br />\n";
            
            $sresult = dbquery("SELECT te.*, tec.*, user_id, user_name, user_status, user_avatar, user_joined, user_level
            FROM ".DB_CALENDAR_EVENTS." te
            INNER JOIN ".DB_CALENDAR_CATS." tec ON te.calendar_cat_id=tec.calendar_cat_id
            LEFT JOIN ".DB_USERS." tu ON te.event_name=tu.user_id
            ".(multilang_table("CA") ? "WHERE ".in_group('te.event_language', LANGUAGE)." AND " : "WHERE ")
            .groupaccess('te.event_visibility')." AND ".Search_Engine::search_conditions('calendar').$date_search.$sortby, Search_Engine::get_param('search_param'));
               
            $search_result = '';

            while ($data = dbarray($sresult)) {
                $data['event_description'] = strip_tags(htmlspecialchars_decode($data['event_description']));
                $text_all = $data['event_description'];
                $text_all = Search_Engine::search_striphtmlbbcodes($text_all);
                $text_frag = Search_Engine::search_textfrag($text_all);
                $subj_c = Search_Engine::search_stringscount($data['event_title']);
                $text_c = Search_Engine::search_stringscount($data['event_description']);

                $context = "<div class='quote' style='width:auto;height:auto;overflow:auto'>".$text_frag."</div><br />";
                $criteria = "<span class='small'>".$subj_c." ".($subj_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['ca403']." ".$locale['ca404'].", ";
                $criteria .= $text_c." ".($text_c == 1 ? $locale['520'] : $locale['521'])." ".$locale['ca403']." ".$locale['ca405']."</span>";

                $meta = "<div class='small'>".Functions::cdatetime($data, ['show_location' => TRUE])."</div>\n";
                $meta .= "<div class='m-t-5'>".html_entity_decode($data['event_description'])."</div>\n";
                $meta .= "<div class='small m-b-10'>".$locale['global_070'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</div>\n";

                $search_result .= strtr(Search::render_search_item(), [
                        '{%item_url%}'             => INFUSIONS."calendar/calendar.php?view=list&event_id=".$data['event_id'],
                        '{%item_target%}'          => '',
                        '{%item_image%}'           => '',
                        '{%item_title%}'           => $data['event_title'],
                        '{%item_description%}'     => $meta,
                        '{%item_search_criteria%}' => $criteria,
                        '{%item_search_context%}'  => $context,
                    ]
                );
            }

            // Pass strings for theme developers
            $formatted_result = strtr(Search::render_search_item_wrapper(), [
                '{%image%}'          => "<img src='".ImageRepo::getimage('ac_CA')."' alt='".$locale['ca400']."' style='width:32px;'/>",
                '{%icon_class%}'     => "fa fa-question-circle fa-lg fa-fw",
                '{%search_title%}'   => $locale['ca400'],
                '{%search_result%}'  => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }
        Search_Engine::search_navigation($rows);
        Search_Engine::search_globalarray($formatted_result);
        Search_Engine::append_item_count($item_count);
    }
}
