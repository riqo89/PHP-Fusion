<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/templates.php
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

use PHPFusion\Panels;
use PHPFusion\Calendar\Calendar;
use PHPFusion\Calendar\Functions;

// Calendar views
if (!function_exists('display_main_calendar')) {
    function display_main_calendar($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".CALENDAR_CSS."calendar.css'>");     

        $html = \PHPFusion\Template::getInstance('main_calendar');
        $html->set_template(CALENDAR_TEMPLATES.'calendar_main.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['calendar_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        
        if (isset($_GET['year']) && !empty($_GET['year'])) {
            $html->set_tag('calendar_view', main_calendar_year($_GET['year'], $info['calendar_items']));
        } elseif (isset($_GET['day']) && !empty($_GET['day'])) {                
            $html->set_tag('calendar_view', main_calendar_day($_GET['day'], $info['calendar_items']));
        } elseif (isset($_GET['month']) && !empty($_GET['month'])) {   
            $html->set_tag('calendar_view', main_calendar_month($_GET['month'], $info['calendar_items']));
        } else {   
            $html->set_tag('calendar_view', main_calendar_month(showdate("%Y-%m", TIME), $info['calendar_items']));
        } 
 
        echo $html->get_output();

        Panels::addPanel('calendar_menu_panel', display_calendar_menu(), Panels::PANEL_RIGHT, iGUEST, 0);
    }
}

// Calendar Categories
if (!function_exists('display_calendar_cats')) {
    function display_calendar_cats($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".CALENDAR_CSS."calendar.css'>");

        $html = \PHPFusion\Template::getInstance('calendar_cats');
        $html->set_template(CALENDAR_TEMPLATES.'calendar_cats.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['calendar_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('cat_locale', $info['cat_locale']);

        if (!empty($info['calendar_categories'])) {
            foreach ($info['calendar_categories'] as $cat_data) {
                $html->set_block('categories', [
                    'calendar_cat_id'          => $cat_data['calendar_cat_id'],
                    'calendar_cat_link'        => $cat_data['calendar_cat_link'],
                    'calendar_cat_name'        => $cat_data['calendar_cat_name'],
                    'calendar_cat_description' => $cat_data['calendar_cat_description'],
                    'calendar_cat_ical'        => "<a href='".$cat_data['ical']['link']."' target='_blank' title='".$cat_data['ical']['title']."'><i class='fa fa-fw fa-calendar-plus text-muted'></i></a>",
                    'events_title'             => "<i class='far fa-calendar-alt m-r-5'></i>".fusion_get_locale('calendar_0332'), 
                    'events_list'              => events_list([
                                                        'where' => '(ce.event_start >= '.TIME.' OR ce.event_end >= '.TIME.') AND ce.calendar_cat_id = '.$cat_data['calendar_cat_id'],
                                                        'orderby'   => 'ce.event_start ASC',
                                                        'limit'     => 3
                                                        ])
                    
                ]);
            }
        } else {
            $html->set_block('no_item', ['message' => fusion_get_locale('calendar_0112a')]);
        }

        echo $html->get_output();

        Panels::addPanel('calendar_menu_panel', display_calendar_menu(), Panels::PANEL_RIGHT, iGUEST, 0);
    }
}

// Calendar Archive
if (!function_exists('display_calendar_archive')) {
    function display_calendar_archive($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".CALENDAR_CSS."calendar.css'>");

        $output = '';
        $months = explode("|", fusion_get_locale('months'));

        $html = \PHPFusion\Template::getInstance('calendar_archive');
        $html->set_template(CALENDAR_TEMPLATES.'calendar_archive.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['calendar_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('cat_locale', $info['cat_locale']);
        $html->set_tag('filter', Functions::calendar_filter(['filter_month' => FALSE, 'form_hidden' => ['view'  => 'archive']]));

        if (!empty($info['calendar_items'])) {
            foreach ($info['calendar_items'] as $key => $calendar_data) {
                $year   = date("Y", $calendar_data['event_start']);
                $month  = date("n", $calendar_data['event_start']);
                $archive_data[$year][$month][] = $calendar_data;
            }
        }

        if (!empty($archive_data)) {
            foreach ($archive_data as $year => $year_data) {
                $output .= '<h2>'.$year.'</h2>';
                foreach($year_data as $month => $month_data) {
                    $output .= '<h3>'.$months[$month].'</h3>';
                    foreach($month_data as $event) {
                        $output .= '<div class="row">';
                        $output .= '<div class="col-md-3">
                                        <i class="fas fa-tag m-r-5" title="'.fusion_get_locale('calendar_0360').'"></i>
                                        <a title="'.strip_tags($event['event_title']).'" href="'.clean_request("view=list&event_id=".$event['event_id'], ['cat_id']).'">'.trimlink(strip_tags($event['event_title']), 50).'</a>
                                    </div>';
                        $output .= '<div class="col-md-5">
                                        <i class="fas fa-align-left m-r-5" title="'.fusion_get_locale('calendar_0361').'"></i>
                                        <span title="'.strip_tags($event['event_description']).'">'.trimlink(strip_tags(parse_textarea($event['event_description'])), 75).'</span>
                                    </div>';
                        $output .= '<div class="col-md-2">
                                    <i class="fas fa-folder m-r-5" title="'.fusion_get_locale('calendar_0362').'"></i>
                                    <span title="'.strip_tags($info['calendar_categories'][$event['calendar_cat_id']]['calendar_cat_name']).'">'.trimlink($info['calendar_categories'][$event['calendar_cat_id']]['calendar_cat_name'], 50).'</span>
                                </div>';
                        $output .= '<div class="col-md-2"><i class="fas fa-calendar-day m-r-5" title="'.fusion_get_locale('calendar_0363').'"></i>'.Functions::showcdate('shortdate', $event['event_start']).'</div>';
                        $output .= '</div>';
                        $output .= '<hr class="m-5" />';
                    }
                }
            }
        }
        
        if (!empty($output)) {
            $html->set_block('archive', [
                'events'    => $output
            ]);
        } else {
            $html->set_block('no_item', ['message' => fusion_get_locale('calendar_0112')]);
        }

        $html->set_tag('pagenav', $info['calendar_nav']);

        echo $html->get_output();

        Panels::addPanel('calendar_menu_panel', display_calendar_menu(), Panels::PANEL_RIGHT, iGUEST, 0);
    }
}


// Calendar List
if (!function_exists('display_calendar_items')) {
    function display_calendar_items($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".CALENDAR_CSS."calendar.css'>"); 
        $locale = fusion_get_locale('', CALENDAR_LOCALE); $i = 0;

        $html = \PHPFusion\Template::getInstance('calendar_item');
        $html->set_template(CALENDAR_TEMPLATES.'calendar_items.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['calendar_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('cat_locale', $info['cat_locale']);
        $html->set_tag('cat_top', $info['cat_top']);
        $html->set_tag('calendar_get_name', $info['calendar_get_name']);
        $html->set_tag('filter', !isset($_GET['event_id']) ? Functions::calendar_filter(['form_hidden'   => ['view'  => 'list']]) : '');

        if (!empty($info['calendar_items'])) {
            add_to_jquery('$(".top").on("click",function(e) {e.preventDefault();$("html, body").animate({scrollTop:0},100);});');
            foreach ($info['calendar_items'] as $key => $calendar_data) {
                
                if(get("attachment_id", FILTER_VALIDATE_INT)) {
                    Functions::check_attachment_request(get("attachment_id", FILTER_VALIDATE_INT));
                }                

                $html->set_block('calendar', [
                    'event_collapse'        => $i++ == 0 ? "in" : "",
                    'event_id'              => $calendar_data['event_id'],
                    'event_title'           => $calendar_data['event_title'],
                    'event_start_title'     => Functions::showcdate("longdate", $calendar_data['event_start']),
                    'event_date'            => Functions::cdatetime($calendar_data),
                    'event_description'     => !empty($calendar_data['event_description']) ? parse_textarea($calendar_data['event_description']) : '',
                    'event_location'        => !empty($calendar_data['event_location']) ? $calendar_data['event_location'] : '',
                    'event_location_map'    => get_settings("calendar", "calendar_show_gmaps_iframe") && !empty($calendar_data['event_location']) ? Functions::google_maps_iframe($calendar_data['event_location']) : '',
                    'event_organizer'       => profile_link($calendar_data['user_id'], $calendar_data['user_name'], $calendar_data['user_status']),
                    'event_participation'   => showparticipationform($calendar_data),
                    'event_attachment'      => (!empty($calendar_data['event_attachment_file']) OR !empty($calendar_data['event_attachment_url'])) ? "<a href='".clean_request('attachment_id='.$calendar_data['event_id'], ['view'])."'>".$locale['calendar_0320']."</a>" : '',

                    'show_description'      => empty($calendar_data['event_description']) ? "hidden" : "",
                    'show_location'         => empty($calendar_data['event_location']) ? "hidden" : "",
                    'show_attachment'       => (empty($calendar_data['event_attachment_file']) AND empty($calendar_data['event_attachment_url'])) ? "hidden" : "",

                    'event_cat'             => $info['calendar_categories'][$calendar_data['calendar_cat_id']]['calendar_cat_name'],
                    'event_datestamp'       => timer($calendar_data['event_datestamp']),

                    'ical_link'             => "<a href='".$calendar_data['ical']['link']."' target='_blank' title='".$calendar_data['ical']['title']."'><i class='fa fa-fw fa-calendar-plus'></i></a>",
                    'edit_link'             => !empty($calendar_data['edit']['link']) ? "<a href='".$calendar_data['edit']['link']."' title='".$calendar_data['edit']['title']."'><i class='fa fa-fw fa-pencil m-l-10'></i></a>" : '',
                    'delete_link'           => !empty($calendar_data['delete']['link']) ? "<a href='".$calendar_data['delete']['link']."' title='".$calendar_data['delete']['title']."'><i class='fa fa-fw fa-trash m-l-10'></i></a>" : '',
                ]);
            }
        } else {
            $html->set_block('no_item', ['message' => fusion_get_locale('calendar_0112')]);
        }
        $html->set_tag('pagenav', $info['calendar_nav']);

        echo $html->get_output();

        Panels::addPanel('calendar_menu_panel', display_calendar_menu(), Panels::PANEL_RIGHT, iGUEST, 0);
    }
}

//Calendar Submissions
if (!function_exists('display_calendar_submissions')) {
    function display_calendar_submissions($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".CALENDAR_CSS."calendar.css'>"); 

        $html = \PHPFusion\Template::getInstance('calendar_submissions');
        $html->set_template(CALENDAR_TEMPLATES.'calendar_submissions.html');
        $html->set_tag('opentable', fusion_get_function('opentable', $info['calendar_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));

        add_to_jquery("
        $('#event_allday').click(function() {
            if ($(this).is(':checked')) {
                $('#event_start-datepicker').datetimepicker().data('DateTimePicker').format('DD.MM.YYYY');  
                $('#event_end-datepicker').datetimepicker().data('DateTimePicker').format('DD.MM.YYYY');
            } else if ($(this).is(':not(:checked)')) {
                $('#event_start-datepicker').datetimepicker().data('DateTimePicker').format('DD.MM.YYYY H:mm');  
                $('#event_end-datepicker').datetimepicker().data('DateTimePicker').format('DD.MM.YYYY H:mm');           
            }                
        });
        ");

        if (!empty($info['item'])) {
            $html->set_block('calendar_submit', [
                'guidelines'        => $info['item']['guidelines'],
                'openform'          => $info['item']['openform'],
                'closeform'         => closeform(),
                'event_title'       => $info['item']['event_title'],
                'event_description' => $info['item']['event_description'],
                'event_allday'      => $info['item']['event_allday'],
                'event_start'       => $info['item']['event_start'],
                'event_end'         => $info['item']['event_end'],                 
                'event_location'    => $info['item']['event_location'],                 
                'event_attachment'  => $info['item']['event_attachment'],
                'calendar_cat_id'   => $info['item']['calendar_cat_id'],
                'event_language'    => $info['item']['event_language'],
                'calendar_submit'   => $info['item']['calendar_submit']
            ]);
        }

        if (!empty($info['confirm'])) {
            $html->set_block('calendar_confirm_submit', [
                'title'       => $info['confirm']['title'],
                'submit_link' => $info['confirm']['submit_link'],
                'index_link'  => $info['confirm']['index_link']
            ]);
        }

        if (!empty($info['no_submissions'])) {
            $html->set_block('calendar_no_submit', ['text' => $info['no_submissions']]);
        }

        echo $html->get_output();
    }
}

if (!function_exists('main_calendar_year')) {
    function main_calendar_year($date = 0, $events = [], $options = []) {

        $locale = fusion_get_locale('', CALENDAR_LOCALE);

        $default_options = [
            'show_title'        => TRUE,
            'show_filters'      => TRUE,
            'mark_events'       => TRUE,
            'shorten_weekdays'  => 2,
        ];
        $options += $default_options;

        $date = !$date ? TIME : mktime(0, 0, 0, 1, 1, $date);;
        $months = explode("|", fusion_get_locale('months'));

        $output = "<div class='container-fluid calendar-year p-0'>\n";

        if ($options['show_title'])     $output .= Functions::calendar_title($date, ['interval' => 'year']);
        if ($options['show_filters'])   $output .= Functions::calendar_filter(['filter_month' => FALSE, 'filter_year' => TRUE]);

        $output .= "<div class='clearfix'></div>\n";
        for ($m = 1; $m <= 12; $m++) {
            $mdate = mktime(0, 0, 0, $m, 1, date('Y', $date));
            $output .= in_array($m, [1, 5, 9]) ? "<div class='row'>\n<div class='col-md-3'>\n" : "<div class='col-md-3'>\n";
            $output .= "<h4 class='display-inline-block'><a href='".clean_request("month=".showdate("%Y-%m", $mdate), ['cat_id'])."'>".$months[$m]."</a></h4>\n";
            $output .= main_calendar_month(date('Y-m', $mdate), $events , [
                        'show_title'        => FALSE,
                        'show_filters'      => FALSE,
                        'list_events'       => FALSE,
                        'mark_events'       => $options['mark_events'],
                        'shorten_weekdays'  => $options['shorten_weekdays'],
                        'sum_days'          => 42
                        ]);
            $output .= in_array($m, [4, 8, 12]) ? "</div>\n</div>\n" : "</div>\n";
        }

        $output .= "</div>\n";
        return $output;
    }
}

if (!function_exists('main_calendar_month')) {
    function main_calendar_month($date = 0, $events = [], $options = []) {

        $locale = fusion_get_locale('', CALENDAR_LOCALE);

        $default_options = [
            'show_title'        => TRUE,
            'show_filters'      => TRUE,
            'list_events'       => TRUE,
            'mark_events'       => FALSE,
            'shorten_weekdays'  => 0,
            'sum_days'          => 0
        ];
        $options += $default_options;

        $date           = !$date ? TIME : strtotime($date);
        $days           = date("t", $date);
        $date_first     = mktime(0, 0, 0, date("n", $date), 1, date("Y", $date));
        $date_last      = mktime(0, 0, 0, date("n", $date), $days, date("Y", $date));
        $day_first      = date("N", $date_first);
        $day_last       = date("N", $date_last);
        $days_before    = $day_first - fusion_get_settings('week_start');
        $days_after     = 6 - $day_last + fusion_get_settings('week_start');
        
        if ($days_before < 0)       $days_before += 7;
        elseif ($days_before > 6)   $days_before -= 7;
        if ($days_after > 6)        $days_after -= 7;

        $sum_days = $options['sum_days'] ?: $days + $days_before + $days_after;

        $weekdays = explode("|", fusion_get_locale('weekdays', LOCALE.LOCALESET.'global.php'));
        for ($i = 0; $i < fusion_get_settings('week_start'); $i++) array_push($weekdays, array_shift($weekdays));

        if (!function_exists('substr_array')) {
            function substr_array(&$item, $key, $length) {
                $item = substr($item, 0, $length);
            }
        }

        if ($options['shorten_weekdays'])   array_walk($weekdays, 'substr_array', $options['shorten_weekdays']);
        if ($options['show_title'])         $output .= Functions::calendar_title($date, ['interval' => 'month']);
        if ($options['show_filters'])       $output .= Functions::calendar_filter();

        $output = "<div class='clearfix'></div>\n";
        $output .= "<div class='table-responsive'>\n<table class='table table-bordered table-striped'>\n<thead>\n<tr>\n";
        foreach ($weekdays as $weekday) $output .= "<th style='width: 14.25%'>".$weekday."</th>";
        $output .= "</tr>\n</thead>\n<tbody>\n";

        for ($i = 0; $i < $sum_days; $i++) {
            $current_day = $i - $days_before;        
            $current_day_ts = strtotime($current_day." day", $date_first);

            if (showdate("%Y-%m-%d", TIME) == showdate("%Y-%m-%d", $current_day_ts)) $class = "current-day";
            elseif ($current_day >= 0 AND $current_day < $days) $class = "normal-day";
            else $class = "other-day";

            $output .= showdate("%w", $current_day_ts) == fusion_get_settings('week_start') ? "<tr class='calendar-row'>\n<td class='".$class."'>\n" : "<td class='".$class."'>\n";
            $output .= "<div class='calendar-day'><a href='".clean_request("day=".showdate("%Y-%m-%d", $current_day_ts), ['cat_id'])."' class='calendar-link-day'>".showdate("%d", $current_day_ts)."</a></div>\n";

            if (!empty($events)) {
                foreach($events as $key => $event) {
                    if (showdate("%Y-%m-%d", $current_day_ts) >= showdate("%Y-%m-%d", $event['event_start']) && showdate("%Y-%m-%d", $current_day_ts) <= showdate("%Y-%m-%d", $event['event_end'])) {
                        if ($options['list_events']) $output .= "<a href='".clean_request("view=list&event_id=".$event['event_id'], ['cat_id'])."' class='item-list calendar-link-item'>".$event['event_title']."</a>\n";
                        if ($options['mark_events']) $output .= "<div class='item-mark'><i class='fas fa-circle'></i></div>\n";
                    }
                }
            }

            $output .= showdate("%w", $current_day_ts) == showdate("%w", strtotime(6 - fusion_get_settings('week_start')." day", $current_day_ts)) ? "</td>\n</tr>\n" : "</td>\n";
        }
        $output .= "</tbody>\n</table>\n</div>\n";

        return $output;

    }
}

if (!function_exists('main_calendar_day')) {
    function main_calendar_day($date = 0, $events = [], $options = []) {

        $locale = fusion_get_locale('', CALENDAR_LOCALE);

        $default_options = [
            'show_title'    =>     TRUE,
            'show_filters'  =>     TRUE
        ];
        $options += $default_options;

        $date = !$date ? TIME : strtotime($date);

        $output = '';
        if ($options['show_title'])     $output .= Functions::calendar_title($date, ['interval' => 'day']);
        if ($options['show_filters'])   $output .= Functions::calendar_filter(['filter_month' => FALSE, 'filter_year' => FALSE, 'form_hidden' => ['day' => isset($_GET['day']) ? $_GET['day'] : '']]);

        $output .= "<table class='table table-bordered table-striped'>\n<thead>\n<tr>\n";
        $output .= "<th style='width: 75px;' class='text-center'>".$locale['calendar_0265']."</th>\n<th>\n";
        if (!empty($events)) {
            foreach($events as $key => $event) {
                if (showdate("%Y-%m-%d", $date) >= showdate("%Y-%m-%d", $event['event_start']) && showdate("%Y-%m-%d", $date) <= showdate("%Y-%m-%d", $event['event_end']) && $event['event_allday']) {              
                    $output .= "<div class='pull-left m-r-30'>\n";
                    $output .= "<a href='".clean_request("view=list&event_id=".$event['event_id'], ['cat_id'])."' class='calendar-link-item'>".$event['event_title']."</a>\n";
                    $output .= "<div class='small'>".Functions::cdatetime($event, ['show_location' => TRUE, 'show_allday' => FALSE])."</div>\n";
                    $output .= "</div>\n";  
                }
            }
        }       
        $output .= "</th>\n</tr>\n</thead>\n<tbody>\n";

        for ($i = 0; $i < 24; $i++) {
            $time = Functions::cdateoffset(mktime($i, 0, 0, date("n", $date), date("j", $date), date("Y", $date)), FALSE);

            $output .= "<tr>\n";
            $output .= "<td class='text-center'>\n".Functions::showcdate("shorttime", $time)."</td>\n<td>\n";
            if (!empty($events)) {
                foreach($events as $key => $event) {
                    $event['event_start2'] = mktime(date("H", $event['event_start']), 0, 0, date("n", $event['event_start']), date("j", $event['event_start']), date("Y", $event['event_start']));
                    if ($time >= $event['event_start2'] && $time <= $event['event_end'] && !$event['event_allday']) {
                        if (date("G", $time) >= date("G", $event['event_start2']) && date("G", $time) <= date("G", $event['event_end'])) {
                            $output .= "<div class='pull-left m-r-30'>\n";
                            $output .= "<a href='".clean_request("view=list&event_id=".$event['event_id'], ['cat_id'])."' class='calendar-link-item'>".$event['event_title']."</a>\n";
                            $output .= "<div class='small'>".Functions::cdatetime($event, ['show_location' => TRUE, 'show_allday' => FALSE, 'show_time_only' => TRUE])."</div>\n";
                            $output .= "</div>\n";
                        }
                    }
                }
            }
            $output .= "</td>\n</tr>\n";
        }
        $output .= "</tbody>\n</table>\n";

        return $output;
    }
}

//Calendar right panel menu
if (!function_exists('display_calendar_menu')) {
    function display_calendar_menu() {

        $locale = fusion_get_locale('', CALENDAR_LOCALE);

        $menu = [
            'day'       =>  [
                'title' => $locale['calendar_0310'],
                'link'  => clean_request("day=".date("Y-m-d", TIME), ['cat_id']),
                'icon'  => 'fas fa-calendar-day',
                'class' => ''
            ],
            'month'     =>  [
                'title' => $locale['calendar_0311'],
                'link'  => clean_request("month=".date("Y-m", TIME), ['cat_id']),
                'icon'  => 'fas fa-calendar-week',
                'class' => ''
            ],
            'year'     =>  [
                'title' => $locale['calendar_0312'],
                'link'  => clean_request("year=".date("Y", TIME), ['cat_id']),
                'icon'  => 'fas fa-calendar-alt',
                'class' => ''
            ],
            'list'      =>  [
                'title' => $locale['calendar_0313'],
                'link'  => clean_request("view=list",  ['cat_id']),
                'icon'  => 'fas fa-list',
                'class' => ''
            ],
            'archive'   =>  [
                'title' => $locale['calendar_0315'],
                'link'  => clean_request("view=archive",  ['cat_id']),
                'icon'  => 'fas fa-archive',
                'class' => ''
            ],
            'cats'      =>  [
                'title' => $locale['calendar_0314'],
                'link'  => clean_request("view=cats"),
                'icon'  => 'fas fa-folder',
                'class' => ''
            ]
        ];

        ob_start();
        openside();
        echo "<div class='pull-right'>\n<a class='text-muted' title='".$locale['calendar_0366']."' href='".CALENDAR."ical.php'><i class='fas fa-calendar-plus'></i></a>\n</div>\n";
        echo "<ul class='block calendar-filter'>\n";
        foreach ($menu as $key => $link) {
            echo "<li class='".(isset($_GET[$key]) || (isset($_GET['view']) && $_GET['view'] == $key) ? "active strong" : "")."'>";
            echo "<a class='".$link['class']."' title='".$link['title']."' href='".$link['link']."'><i class='".$link['icon']." m-r-5'></i>".$link['title']."</a>";
            echo "</li>\n";
        }
        echo "</ul>\n";
        closeside();

        openside($locale['calendar_0332']);
        echo events_list();
        closeside();

        return ob_get_clean();
    }
}

//Calendar events list
if (!function_exists('events_list')) {
    function events_list($filters = []) {

        $default_filters = [
            'where'     => '(ce.event_start >= '.TIME.' OR ce.event_end >= '.TIME.')',
            'orderby'   => 'ce.event_start ASC',
            'limit'     => 3
        ]; 

        $filters += $default_filters;

        $items = Calendar::get_CalendarData($filters);

        ob_start();        
        echo "<ul class='block event-items'>\n";
        if (!empty($items['calendar_items'])) {
            foreach ($items['calendar_items'] as $key => $item) {
                echo "<li>\n";
                echo "<a title='".$locale['calendar_1001']."' href='".CALENDAR."calendar.php?view=list&event_id=".$item['event_id']."'>".$item['event_title']."</a>\n";
                echo "<div class='event-date'>".Functions::cdatetime($item, ['show_location' => TRUE])."</div>\n";
                echo "</li>\n";
            }
        } else {
            echo "<div>".fusion_get_locale('calendar_0333')."</div>\n";
        }
        echo "</ul>\n";

        return ob_get_clean();
    }
}

//Calendar participation modal form
if (!function_exists('showparticipationform')) {
    function showparticipationform($info = []) {

        $html = '';
        $locale = fusion_get_locale('', CALENDAR_LOCALE);

        $p_result = dbquery("SELECT * FROM ".DB_CALENDAR_PARTICIPATION."
        WHERE participant_name='".fusion_get_userdata('user_id')."' AND event_id='".$info['event_id']."'
        ORDER BY participant_status ASC");

        $p2_result = dbquery("SELECT cp.*, cu.user_id, cu.user_name, cu.user_status, cu.user_avatar, cu.user_level, cu.user_joined
        FROM ".DB_CALENDAR_PARTICIPATION." cp
        LEFT JOIN ".DB_USERS." AS cu ON cp.participant_name=cu.user_id
        WHERE cp.event_id='".$info['event_id']."'
        ORDER BY cp.participant_status ASC, cu.user_level ASC");

        if (dbrows($p_result) > 0) {
            $participant_data = dbarray($p_result);
        } else {
            $participant_data = [
                'participant_id'        => 0,
                'event_id'              => $info['event_id'],
                'participant_name'      => fusion_get_userdata('user_id'),
                'participant_status'    => 0,
                'participant_note'      => '',
                'participant_datestamp' => TIME,
            ];
        }

        if (isset($_POST['save_and_close'])) {

            $participant_data = [
                'participant_id'        => form_sanitizer($_POST['participant_id'], 0, 'participant_id'),
                'event_id'              => form_sanitizer($_POST['event_id'], 0, 'event_id'),
                'participant_name'      => fusion_get_userdata('user_id'),
                'participant_status'    => form_sanitizer($_POST['participant_status'], 0, 'participant_status'),
                'participant_note'      => form_sanitizer($_POST['participant_note'], '', 'participant_note'),
                'participant_datestamp' => TIME,
            ];

           // Handle
           if (\defender::safe()) {
                // Update
                if (dbcount("(participant_id)", DB_CALENDAR_PARTICIPATION, "participant_id='".$participant_data['participant_id']."' AND event_id='".$participant_data['event_id']."'")) {
                    dbquery_insert(DB_CALENDAR_PARTICIPATION, $participant_data, 'update');
                    addNotice('success', $locale['calendar_0329']);

                // Create
                } else {
                    $participant_data['participant_id'] = dbquery_insert(DB_CALENDAR_PARTICIPATION, $participant_data, 'save');
                    addNotice('success', $locale['calendar_0328']);
                }
                redirect(clean_request('view=list', ['event_id']), FALSE);
            }
        }

        if (iMEMBER && get_settings('calendar', 'calendar_allow_participation') && $info['event_participation']) {

            $status_class = [
                0       => ['btn-default', 'fas fa-circle m-r-10'],
                1       => ['btn-success', 'fas fa-check m-r-10'],
                2       => ['btn-warning', 'fas fa-question m-r-10'],
                3       => ['btn-danger', 'fas fa-times m-r-10']
            ];

            $options = [
                1       => '<i class="fas fa-check m-r-5"></i>'.$locale['calendar_0324'],
                2       => '<i class="fas fa-question m-r-5"></i>'.$locale['calendar_0325'],
                3       => '<i class="fas fa-times m-r-5"></i>'.$locale['calendar_0326']
            ];

            $count_participations = [
                1       => dbcount("(participant_id)", DB_CALENDAR_PARTICIPATION, "event_id='".$info['event_id']."' AND participant_status='1'"),
                2       => dbcount("(participant_id)", DB_CALENDAR_PARTICIPATION, "event_id='".$info['event_id']."' AND participant_status='2'"),
                3       => dbcount("(participant_id)", DB_CALENDAR_PARTICIPATION, "event_id='".$info['event_id']."' AND participant_status='3'")
            ];


            $html = "<a class='btn ".$status_class[$participant_data['participant_status']][0]."'  href='#participate' id='event_participation-".$participant_data['event_id']."'>";
            $html .= "<i class='".$status_class[$participant_data['participant_status']][1]."'></i>".sprintf($locale['calendar_0322'], implode("/", $count_participations));
            $html .= "</a>\n";

            $modal = openmodal('event_participation-'.$participant_data['event_id'], sprintf($locale['calendar_0323'], $info['event_title']), ['button_id' => 'event_participation-'.$participant_data['event_id']]);
            
            $tab_title['title'][] = $locale['calendar_0330'];
            $tab_title['id'][] = 'participants-'.$info['event_id'];
            $tab_title['icon'][] = 'fas fa-users m-r-5';

            $tab_title['title'][] = $locale['calendar_0331'];
            $tab_title['id'][] = 'my-participation-'.$info['event_id'];
            $tab_title['icon'][] = 'fas fa-user-clock m-r-5';

            $tab_active = tab_active($tab_title, dbcount("(participant_id)", DB_CALENDAR_PARTICIPATION, "event_id='".$info['event_id']."' AND participant_name='".fusion_get_userdata('user_id')."'") ? 0 : 1);

            $modal .= opentab($tab_title, $tab_active, 'participationstab');
            $modal .= opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);

            $modal .= "<div class='table-responsive'><table class='table table-striped table-hover'>\n";
            $modal .= "<thead>\n";
            $modal .= "<tr>\n";
            $modal .= "<th class='col-xs-1'>".$locale['calendar_0335']."</th>\n";
            $modal .= "<th class='col-xs-2'>".$locale['calendar_0336']."</th>\n";
            $modal .= "<th class='col-xs-4'>".$locale['calendar_0337']."</th>\n";
            $modal .= "<th class='col-xs-5'>".$locale['calendar_0338']."</th>\n";
            $modal .= "</tr>\n";
            $modal .= "</thead>\n";

            if (dbrows($p2_result) > 0) {
                while ($participant = dbarray($p2_result)) {

                    $modal .= "<tr>\n<td class='col-xs-1'>".display_avatar($participant, '35px', '', TRUE, 'img-rounded')."</td>\n";
                    $modal .= "<td class='col-xs-2'>\n";
                    $modal .= "<span class='side'>".profile_link($participant['user_id'], $participant['user_name'], $participant['user_status'])."</span>\n";
                    $modal .= "<div class='small'>".timer($participant['participant_datestamp'])."</div>\n";
                    $modal .= "</td>\n";
                    $modal .= "<td class='col-xs-4'>".$options[$participant['participant_status']]."</td>\n";
                    $modal .= "<td class='col-xs-5'>".(!empty($participant['participant_note']) ? parse_textarea($participant['participant_note']) : $locale['calendar_0339'])."</td>\n</tr>\n";
                }
            } else {
                $modal .= "<td colspan='5' class='col-xs-3 text-center'>".$locale['calendar_0340']."</td>\n";
            }

            $modal .= "</table>\n</div>\n";
            $modal .= closetabbody();

            $modal .= opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active);
            $modal .= openform('inputform', 'post', clean_request('view=list', ['event_id']), ['class' => 'spacer-sm']);
            $modal .= form_hidden('participant_id', '', $participant_data['participant_id']);
            $modal .= form_hidden('event_id', '', $participant_data['event_id']);
            
            $modal .= form_checkbox('participant_status', $locale['calendar_0341'], $participant_data['participant_status'], [
                'type'              => 'radio',
                'inline'            => TRUE,
                'inline_options'    => TRUE,
                'options'           => $options,
                'class'             => 'm-t-20'
            ]);

            $modal .= form_textarea('participant_note', '', $participant_data['participant_note'], [
                'type'        => 'text',
                'height'      => '100px',
                'class'       => 'm-t-10',
                'placeholder' => $locale['calendar_0327']
            ]);

            $modal .= form_button('save_and_close', $locale['save_and_close'], $locale['save_and_close'], [
                'class' => 'btn-primary btn-sm m-l-5',
                'icon' => 'fa fa-floppy-o',
                'input_id' => 'save_and_close-'.$participant_data['event_id']
            ]);

            $modal .= closeform();
            $modal .= closetabbody();
            $modal .= closetab();  

            $modal .= closemodal();
            add_to_footer($modal);
        }

        return $html;
    }
}