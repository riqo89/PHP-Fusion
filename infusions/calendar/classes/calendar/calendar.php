<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/classes/calendar/calendar.php
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

use PHPFusion\BreadCrumbs;
use PHPFusion\SiteLinks;

/**
 * Class Calendar
 *
 * @package PHPFusion\Calendar
 */
abstract class Calendar extends CalendarServer {
    private static $locale = [];
    private static $predefined_filters = [];
    public $info = [];

    /**
     * Executes main page information
     *
     * @param int $category
     *
     * @return array
     */
    public function set_CalendarInfo(array $filters = [], array $extended_info = []) {

        $predefined_filters = [
            'where'     => $this->is_catid ? 'ce.calendar_cat_id = '.$this->catid : '',
        ];

        $filters += $predefined_filters;

        self::$locale = fusion_get_locale("", CALENDAR_LOCALE);

        set_title(SiteLinks::get_current_SiteLinks('infusions/calendar/calendar.php', 'link_name'));

        BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS.'calendar/calendar.php',
            'title' => SiteLinks::get_current_SiteLinks('', 'link_name')
        ]);   

        $info = [
            'calendar_categories' => [],
            'calendar_items'      => [],
            'calendar_tablename'  => self::$locale['calendar_0000'],
            'calendar_get'        => 0
        ];

        $info += $extended_info;

        $info = array_merge($info, self::get_CalendarData($filters));

        if ($this->is_list) {
            set_title(SiteLinks::get_current_SiteLinks(INFUSIONS.'calendar/calendar.php', 'link_name'));
            add_to_title(self::$locale['global_201']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'calendar/calendar.php?view=list',
                'title' => self::$locale['calendar_0313']
            ]);
        } elseif ($this->is_archive) {
            set_title(SiteLinks::get_current_SiteLinks(INFUSIONS.'calendar/calendar.php', 'link_name'));
            add_to_title(self::$locale['global_201']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'calendar/calendar.php?view=archive',
                'title' => self::$locale['calendar_0315']
            ]);
        } elseif ($this->is_cat) {
            set_title(SiteLinks::get_current_SiteLinks(INFUSIONS.'calendar/calendar.php', 'link_name'));
            add_to_title(self::$locale['global_201']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'calendar/calendar.php?view=cats',
                'title' => self::$locale['calendar_0314']
            ]);
        } elseif ($this->is_day) {
            set_title(SiteLinks::get_current_SiteLinks(INFUSIONS.'calendar/calendar.php', 'link_name'));
            add_to_title(self::$locale['global_201']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'calendar/calendar.php?day='.$this->day,
                'title' => self::$locale['calendar_0310']
            ]);
        } elseif ($this->is_year) {
            set_title(SiteLinks::get_current_SiteLinks(INFUSIONS.'calendar/calendar.php', 'link_name'));
            add_to_title(self::$locale['global_201']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'calendar/calendar.php?year='.$this->year,
                'title' => self::$locale['calendar_0312']
            ]);
        } else {
            set_title(SiteLinks::get_current_SiteLinks(INFUSIONS.'calendar/calendar.php', 'link_name'));
            add_to_title(self::$locale['global_201']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'calendar/calendar.php?month='.$this->month,
                'title' => self::$locale['calendar_0311']
            ]);
        }

        if ($this->catid && isset($info['calendar_categories'][$this->catid])) {
            set_title(SiteLinks::get_current_SiteLinks(INFUSIONS.'calendar/calendar.php', 'link_name'));
            add_to_title(self::$locale['global_201'].$info['calendar_categories'][$this->catid]['calendar_cat_name']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'calendar/calendar.php?cat_id='.$this->catid,
                'title' => $info['calendar_categories'][$this->catid]['calendar_cat_name']
            ]);
        }
        
        if ($this->eventid && isset($info['calendar_items'][$this->eventid])) {
            set_title(SiteLinks::get_current_SiteLinks(INFUSIONS.'calendar/calendar.php', 'link_name'));
            add_to_title(self::$locale['global_201'].$info['calendar_items'][$this->eventid]['event_title']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'calendar/calendar.php?event_id='.$this->eventid,
                'title' => $info['calendar_items'][$this->eventid]['event_title']
            ]);
        }

        $this->info = $info;

        return (array)$info;
    }

    /**
     * Outputs category variables
     *
     * @param int $cat
     *
     * @return array
     */
    public static function get_CalendarData(array $filters = []) {
        $info = [
            'calendar_items'      => [],
            'cat_locale'          => self::$locale['calendar_0001'],
            'cat_top'             => self::$locale['calendar_0002'],
            'calendar_get_name'   => '',
            'calendar_categories' => [],
        ];

        $default_filters = [
            'where'     => '',
            'groupby'   => 'ce.event_id',
            'orderby'   => 'ce.event_start ASC, ce.calendar_cat_id ASC, ce.event_id ASC',
            'limit'     => '',
            'offset'    => ''
        ];
        
        $filters += $default_filters;

        $c_result = dbquery("SELECT cc.*, count(ce.event_id) 'calendar_count'
            FROM ".DB_CALENDAR_CATS." cc
            LEFT JOIN ".DB_CALENDAR_EVENTS." ce using (calendar_cat_id)
            ".(multilang_table("CA") ? "WHERE ".in_group('calendar_cat_language', LANGUAGE) : "")."
            GROUP BY cc.calendar_cat_id
            ORDER BY calendar_cat_id ASC
        ");

        if (dbrows($c_result)) {
            while ($c_data = dbarray($c_result)) {
                $info['calendar_categories'][$c_data['calendar_cat_id']] = $c_data;
                $info['calendar_categories'][$c_data['calendar_cat_id']]['calendar_cat_link'] = INFUSIONS."calendar/calendar.php?cat_id=".$c_data['calendar_cat_id'];
                $info['calendar_categories'][$c_data['calendar_cat_id']]['ical']['title'] = self::$locale['calendar_0365'];
                $info['calendar_categories'][$c_data['calendar_cat_id']]['ical']['link'] = CALENDAR."ical.php?cat_id=".$c_data['calendar_cat_id'];
                $info['calendar_get'] = $cat;

                if (!empty($info['calendar_categories'][$info['calendar_get']]['calendar_cat_name'])) {
                    $info['calendar_get_name'] = $info['calendar_categories'][$info['calendar_get']]['calendar_cat_name'];
                }
            }
        }
        
        // Get Items
        $result = dbquery("SELECT ce.*,
            cu.user_id, cu.user_name, cu.user_email, cu.user_status, cu.user_avatar, cu.user_level, cu.user_joined
            FROM ".DB_CALENDAR_EVENTS." ce
            LEFT JOIN ".DB_USERS." AS cu ON ce.event_name=cu.user_id
            WHERE ce.event_status='1' AND ".groupaccess("ce.event_visibility").
            (multilang_table("CA") ? " AND ".in_group("ce.event_language", LANGUAGE) : '').
            (!empty($filters['where']) ? " AND ".$filters['where'] : "").
            (!empty($filters['groupby']) ? " GROUP BY ".$filters['groupby'] : "").
            (!empty($filters['orderby']) ? " ORDER BY ".$filters['orderby'] : "").
            (!empty($filters['limit']) ? " LIMIT ".$filters['limit'] : ""));
            
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $data['event_description'] = parse_textarea($data['event_description'], FALSE, FALSE, TRUE, FALSE, $data['event_breaks'] == 'y' ? TRUE : FALSE);
                $info['calendar_items'][$data['event_id']] = $data;
                $info['calendar_items'][$data['event_id']]['ical']['title'] = self::$locale['calendar_0364'];
                $info['calendar_items'][$data['event_id']]['ical']['link'] = CALENDAR."ical.php?event_id=".$data['event_id'];
                $info['calendar_items'][$data['event_id']]['edit']['title'] = (iADMIN && checkrights("CA")) ? self::$locale['edit'] : '';
                $info['calendar_items'][$data['event_id']]['edit']['link'] = (iADMIN && checkrights("CA")) ? INFUSIONS."calendar/calendar_admin.php".fusion_get_aidlink()."&amp;section=calendar&amp;ref=calendar_form&amp;action=edit&amp;cat_id=".$data['calendar_cat_id']."&amp;event_id=".$data['event_id'] : '';
                $info['calendar_items'][$data['event_id']]['delete']['title'] = (iADMIN && checkrights("CA")) ? self::$locale['delete'] : '';
                $info['calendar_items'][$data['event_id']]['delete']['link'] = (iADMIN && checkrights("CA")) ? INFUSIONS."calendar/calendar_admin.php".fusion_get_aidlink()."&amp;section=calendar&amp;ref=calendar_form&amp;action=delete&amp;event_id=".$data['event_id'] : '';
            }
        }

        return (array)$info;
    }

    protected function __clone() {
    }
}
