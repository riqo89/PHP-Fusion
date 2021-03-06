<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/classes/functions.php
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

use DateTime;
use DateTimeZone;
use PHPFusion\httpdownload;

class Functions {

    public static function showcdate($format, $val) {

        if (!empty($val)) {
            if (in_array($format, ['shorttime', 'longtime', 'shortdate', 'longdate', 'titledate', 'titledate_day', 'titledate_month', 'titledate_year'])) {
                $format = get_settings("calendar", "calendar_format_".$format);            
            }
            return "<span title='".showdate("longdate", $val)."'>".showdate($format, $val)."</span>";
        } else {
            return "<span title='".showdate("longdate", TIME)."'>".showdate($format, TIME)."</span>";
        }
    }

    public static function cdatetime($calendar_data = [], $options = []) {

        $default_options = [
            'show_allday'       => TRUE,
            'show_location'     => FALSE,
            'show_time_only'    => FALSE,
            'dateformat'        => 'shortdate',
            'timeformat'        => 'shorttime'
            ];

        $options += $default_options;

        if ($calendar_data['event_allday']) {
            $cdate[] = self::showcdate($options['dateformat'], $calendar_data['event_start']);
            if (date("Y-m-d", $calendar_data['event_start']) != date("Y-m-d", $calendar_data['event_end'])) {
                $cdate[] = "-";
                $cdate[] = self::showcdate($options['dateformat'], $calendar_data['event_end']);
            }
            if ($options['show_allday']) $cdate[] = "(".fusion_get_locale('calendar_0265').")";
        } else {
            if(!$options['show_time_only'] || date("Y-m-d", $calendar_data['event_start']) != date("Y-m-d", $calendar_data['event_end'])) {
                $cdate[] = self::showcdate($options['dateformat'], $calendar_data['event_start']);
            }
            $cdate[] = self::showcdate($options['timeformat'], $calendar_data['event_start']);
            $cdate[] = "-";
            if (date("Y-m-d", $calendar_data['event_start']) != date("Y-m-d", $calendar_data['event_end'])) {    
                $cdate[] = self::showcdate($options['dateformat'], $calendar_data['event_end']);                
            } 
            $cdate[] = self::showcdate($options['timeformat'], $calendar_data['event_end']);
        }
        if ($options['show_location'] && !empty($calendar_data['event_location'])) {
            $cdate[] = "&bull;";
            $cdate[] = $calendar_data['event_location'];
        }

        return implode(' ', $cdate);
    }

    public static function cdateoffset($val, $from_gmt = TRUE) {
        $userdata = fusion_get_userdata();

        if (!empty($userdata['user_timezone'])) {
            $tz_client = $userdata['user_timezone'];
        } else {
            $tz_client = fusion_get_settings('timeoffset');
        }

        if (empty($tz_client)) {
            $tz_client = 'Europe/London';
        }

        $client_dtz = new DateTimeZone($tz_client);
        $client_dt = new DateTime('now', $client_dtz);
        $offset = $client_dtz->getOffset($client_dt);

        if($from_gmt) {
            $offset = intval($val) + $offset;
        } else {
            $offset = intval($val) - $offset;
        }

        return $offset;
    }

    public static function google_maps_iframe($address = '', $options = []) {

        $options_string = '';
        $default_options = [
            'width'         => '100%',
            'height'        => 250,
            'class'         => 'panel panel-default',
            ];

        $options += $default_options;

        foreach($options as $key => $value) $options_string .=  sprintf("%s=\"%s\" ", $key, $value);

        return !empty($address) ? '<iframe '.$options_string.' src="https://maps.google.com/maps?hl='.fusion_get_locale('short_lang_name').'&amp;q='.$address.'&amp;z=15&amp;output=embed"></iframe>' : '';
    }

    public static function check_attachment_request($event_id) {


        $data = dbarray(dbquery("SELECT * FROM ".DB_CALENDAR_EVENTS." WHERE event_id='".$event_id."'"));
        if (checkgroup($data['event_visibility'])) {            
            if (!empty($data['event_attachment_file']) && file_exists(CALENDAR_ATTACHMENTS.$data['event_attachment_file'])) {
                ob_end_clean();
                $object = new httpdownload;
                $object->set_byfile(CALENDAR_ATTACHMENTS.$data['event_attachment_file']);
                $object->use_resume = TRUE;
                $object->download();
                exit;
            } else if (!empty($data['event_attachment_url'])) {
                $url_prefix = (!strstr($data['event_attachment_url'], "http://") && !strstr($data['event_attachment_url'], "https://") ? "http://" : '');
                redirect($url_prefix.$data['event_attachment_url']);
            } else {
                redirect(CALENDAR."calendar.php");
            }
        }
    }

    public static function convert_strftime_dateformat($format, $strftime_to_dateformat = TRUE) {
    
        $caracs = array(
            // Day - no strf eq : S
            'd' => '%d', 'D' => '%a', 'j' => '%e', 'l' => '%A', 'N' => '%u', 'w' => '%w', 'z' => '%j',
            // Week - no date eq : %U, %W
            'W' => '%V', 
            // Month - no strf eq : n, t
            'F' => '%B', 'm' => '%m', 'M' => '%b',
            // Year - no strf eq : L; no date eq : %C, %g
            'o' => '%G', 'Y' => '%Y', 'y' => '%y',
            // Time - no strf eq : B, G, u; no date eq : %r, %X
            'a' => '%P', 'A' => '%p', 'g' => '%l', 'h' => '%I', 'H' => '%H', 'i' => '%M', 's' => '%S', 'H:i' => '%R', 'H:i:s' => '%T',
            // Timezone - no strf eq : e, I, P, Z
            'O' => '%z', 'T' => '%Z',
            // Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x 
            'U' => '%s'
        );

        if ($strftime_to_dateformat) $caracs = array_flip($caracs);

        if (in_array($format, ['shorttime', 'longtime', 'shortdate', 'longdate', 'titledate', 'titledate_day', 'titledate_month', 'titledate_year'])) {
            $format = get_settings("calendar", "calendar_format_".$format);            
        }

        return strtr((string)$format, $caracs);
    }

    public static function calendar_title($date = TIME, $options = []) {

        $default_options = [
            'interval'      => 'month',
            'class'         => 'well',
            'icon_before'   => 'fas fa-arrow-left m-r-10',
            'icon_after'    => 'fas fa-arrow-right m-l-10',
            'icon_now'      => 'far fa-calendar-alt m-r-10'

        ];  
        $options += $default_options;

        $cdate = [
            'now'           => $date,
            'before'        => strtotime("-1 ".$options['interval'], $date),
            'after'         => strtotime("+1 ".$options['interval'], $date),
        ];

        $param = [
            'day'           => ['%Y-%m-%d', 'titledate_day'],
            'month'         => ['%Y-%m', 'titledate_month'],
            'year'          => ['%Y', 'titledate_year']
        ];     

        ob_start();
        echo "<div class='".$options['class']."'>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-md-3 col-xs-6 text-left m-b-10'>\n";
        echo "<a href='".clean_request([$options['interval'] => showdate($param[$options['interval']][0], $cdate['before'])], ['cat_id'])."'><i class='".$options['icon_before']."'></i>".Functions::showcdate($param[$options['interval']][1], $cdate['before'])."</a>\n";
        echo "</div>\n";
        echo "<div class='col-md-3 col-xs-6 col-md-push-6 text-right m-b-10'>\n";
        echo "<a href='".clean_request([$options['interval'] => showdate($param[$options['interval']][0], $cdate['after'])], ['cat_id'])."'>".Functions::showcdate($param[$options['interval']][1], $cdate['after'])."<i class='".$options['icon_after']."'></i></a>\n";
        echo "</div>\n";
        echo "<div class='col-md-6 col-xs-12 col-md-pull-3 text-center'>\n";
        echo "<h3 class='m-0'><i class='".$options['icon_now']."'></i>".Functions::showcdate($param[$options['interval']][1], $cdate['now'])."</h3>\n";
        echo "</div>\n";
        echo "</div>\n</div>\n";

        return ob_get_clean();
    }

    public static function calendar_filter($options = []) {

        $locale = fusion_get_locale('', CALENDAR_LOCALE);

        $default_options = [
            'filter_cat'    => TRUE,
            'filter_month'  => TRUE,
            'filter_year'   => FALSE,
            'form_hidden'   => []
        ]; 
        $options += $default_options;
        
        ob_start();
        echo openform('calendar_filter', 'get', FUSION_REQUEST);
        foreach ($options['form_hidden'] as $key => $value) {
            echo form_hidden($key, '', $value);
        }

        if ($options['filter_year']) {
            echo form_datepicker('year', '', isset($_GET['year']) ? strtotime($_GET['year']."-01") : TIME, [
                'placeholder'       => $locale['calendar_0301'],
                'date_format_js'    => 'YYYY',
                'date_format_php'   => 'Y',
                'class'             => 'pull-left',
                'width'             => '130px'
            ]);

            add_to_jquery("
                $('#year').on('focusout', function(e) {
                    $(this).closest('form').submit();
                });
            ");
        }

        if ($options['filter_month']) {
            echo form_datepicker('month', '', isset($_GET['month']) ? strtotime($_GET['month']) : TIME, [
                'placeholder'       => $locale['calendar_0301'],
                'date_format_js'    => 'YYYY-MM',
                'date_format_php'   => 'Y-m',
                'width'             => '130px',
                'class'             => 'pull-left'
            ]);

            add_to_jquery("
                $('#month').on('focusout', function(e) {
                    $(this).closest('form').submit();
                });
            ");
        }

        if ($options['filter_cat']) {
            echo form_select_tree('cat_id', '', isset($_GET['cat_id']) ? $_GET['cat_id'] : '', [
                'no_root'           => TRUE,
                'allowclear'        => TRUE,
                'placeholder'       => $locale['calendar_0300'],
                'class'             => 'pull-right',
                'inner_width'       => '150px',
                'query'             => multilang_table('CA') ? ' WHERE '.in_group('calendar_cat_language', LANGUAGE) : ''
            ], DB_CALENDAR_CATS, "calendar_cat_name", "calendar_cat_id", 0);
               
            add_to_jquery("
                $('#cat_id').bind('change', function(e) {
                    $(this).closest('form').submit();
                });
            ");
        }

        echo closeform();

        return ob_get_clean();
    }
}