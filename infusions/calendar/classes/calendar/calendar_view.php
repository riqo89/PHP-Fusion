<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/classes/calendar/calendar_view.php
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

/**
 * Controller package
 * Class CalendarView
 *
 * @package PHPFusion\Calendar
 */
class CalendarView extends Calendar {

    /**
     * Displays CALENDAR
     */

    public function display_calendar() {  

        if ($this->is_list) {

            if ($this->is_eventid) {
                $filters = [
                    'where'     => 'ce.event_id = '.$this->eventid
                ];
            } elseif ($this->is_month || $this->is_catid) {
                $condition['cat']   = 'ce.calendar_cat_id = '.$this->catid;
                $condition['month'] = date("Ym", strtotime($this->month)).' BETWEEN FROM_UNIXTIME(ce.event_start, \'%Y%m\') AND FROM_UNIXTIME(ce.event_end, \'%Y%m\')';

                $filters = [
                    'where'     => ($this->is_month && $this->is_catid) ? ($condition['cat'].' AND '.$condition['month']) : ($this->is_month ? $condition['month'] : $condition['cat']),
                    'orderby'   => 'ce.event_start ASC'
                ];  
            } else {      
                $filters = [
                    'where'     => '(ce.event_start >= '.TIME.' OR ce.event_end >= '.TIME.')',
                    'orderby'   => 'ce.event_start ASC'
                ];                                  
            }

            $einfo['calendar_max_rows'] = dbcount("('ce.event_id')", DB_CALENDAR_EVENTS." ce", groupaccess('ce.event_visibility')." AND ce.event_status='1' AND ".$filters['where']);
            $einfo['calendar_rowstart'] = get_rowstart("rowstart", $einfo['calendar_max_rows']);

            $filters += [
                'limit'     => $einfo['calendar_rowstart'].', '.get_settings("calendar", "calendar_pagination")
            ]; 
            
            $einfo['calendar_nav_link'] = clean_request("view=list", ['cat_id', 'month'])."&";  
            $einfo['calendar_nav'] = makepagenav($einfo['calendar_rowstart'], get_settings("calendar", "calendar_pagination"), $einfo['calendar_max_rows'], 3, $einfo['calendar_nav_link']);

            display_calendar_items($this->set_CalendarInfo($filters, $einfo));
        } elseif ($this->is_archive) {

            if ($this->is_catid) {
                $filters = [
                    'where'     => '(ce.event_start < '.TIME.' AND ce.event_end < '.TIME.') AND ce.calendar_cat_id = '.$this->catid,
                    'orderby'   => 'ce.event_start DESC'
                ];  
            } else {
                $filters = [
                    'where'     => '(ce.event_start < '.TIME.' AND ce.event_end < '.TIME.')',
                    'orderby'   => 'ce.event_start DESC'
                ];
            } 
              
            $einfo['calendar_max_rows'] = dbcount("('ce.event_id')", DB_CALENDAR_EVENTS." ce", groupaccess('ce.event_visibility')." AND ce.event_status='1' AND ".$filters['where']);
            $einfo['calendar_rowstart'] = get_rowstart("rowstart", $einfo['calendar_max_rows']);

            $filters += [
                'limit'     => $einfo['calendar_rowstart'].', '.get_settings("calendar", "calendar_pagination_archive")
            ]; 
            
            $einfo['calendar_nav_link'] = clean_request("view=archive", ['cat_id'])."&";  
            $einfo['calendar_nav'] = makepagenav($einfo['calendar_rowstart'], get_settings("calendar", "calendar_pagination_archive"), $einfo['calendar_max_rows'], 3, $einfo['calendar_nav_link']);

            display_calendar_archive($this->set_CalendarInfo($filters, $einfo));
        } elseif ($this->is_cat) {
            display_calendar_cats($this->set_CalendarInfo());
        } else {
            display_main_calendar($this->set_CalendarInfo());
        }
    }

}
