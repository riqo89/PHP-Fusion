<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/classes/server.php
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

class CalendarServer {
    protected static $calendar_settings = [
        'calendar_allow_submission' => 0
    ];
    private static $calendar_instance = NULL;
    private static $calendar_submit_instance = NULL;
    private static $calendar_admin_instance = NULL;
    public $save;
       
    public $catid;
    public $eventid;
    public $day;
    public $month;
    public $year;
    
    public $is_list;
    public $is_archive;
    public $is_cat;
    public $is_year;
    public $is_month;
    public $is_day;

    public $is_catid;
    public $is_eventid; 

    public function __construct() {
        self::$calendar_settings = get_settings("calendar");
        self::globinf();
    }

    public function globinf() {

        $this->save = (string)filter_input(INPUT_POST, 'savesettings', FILTER_DEFAULT);

        $this->catid = isset($_GET['cat_id']) ? $_GET['cat_id'] : 0;
        $this->eventid = isset($_GET['event_id']) ? $_GET['event_id'] : 0;
        $this->day = isset($_GET['day']) ? $_GET['day'] : date("d", TIME);
        $this->month = isset($_GET['month']) ? $_GET['month'] : date("m", TIME);
        $this->year = isset($_GET['year']) ? $_GET['year'] : date("Y", TIME);

        $this->is_list = isset($_GET['view']) && $_GET['view'] == "list" ? 1 : 0;
        $this->is_archive = isset($_GET['view']) && $_GET['view'] == "archive" ? 1 : 0;
        $this->is_cat = isset($_GET['view']) && $_GET['view'] == "cats" ? 1 : 0;
        $this->is_year = isset($_GET['year']) && !empty($_GET['year']) ? 1 : 0;
        $this->is_month = isset($_GET['month']) && !empty($_GET['month']) ? 1 : 0;
        $this->is_day = isset($_GET['day']) && !empty($_GET['day']) ? 1 : 0;  

        $this->is_catid = isset($_GET['cat_id']) && isnum($_GET['cat_id']) && !empty($_GET['cat_id']) ? 1 : 0;
        $this->is_eventid = isset($_GET['event_id']) && isnum($_GET['event_id']) && !empty($_GET['event_id']) ? 1 : 0; 
    }

    public static function Calendar() {
        if (self::$calendar_instance === NULL) {
            self::$calendar_instance = new CalendarView();
        }

        return self::$calendar_instance;
    }

    public static function CalendarSubmit() {
        if (self::$calendar_submit_instance === NULL) {
            self::$calendar_submit_instance = new CalendarSubmissions();
        }

        return self::$calendar_submit_instance;
    }

    public static function CalendarAdmin() {
        if (self::$calendar_admin_instance === NULL) {
            self::$calendar_admin_instance = new CalendarAdminView();
        }

        return self::$calendar_admin_instance;
    }
}
