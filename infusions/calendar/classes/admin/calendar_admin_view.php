<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/classes/admin/calendar_admin_view.php
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

class CalendarAdminView extends CalendarAdminModel {
    private $allowed_pages = ['calendar', 'calendar_cat', 'calendar_cat_form', 'calendar_form', 'submissions', 'settings'];
    private $locale = [];

    public function display_admin() {
        $this->locale = self::get_calendarAdminLocale();

        // Back and Check Section
        if (isset($_GET['section']) && $_GET['section'] == 'back') {
            redirect(clean_request('', ['ref', 'section', 'action', 'event_id', 'cat_id', 'submit_id'], FALSE));
        }
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $this->allowed_pages) ? $_GET['section'] : $this->allowed_pages[0];

        // Sitetitle
        add_to_title($this->locale['calendar_0000']);

        // Handle Breadcrumbs and Titles
        $calendarTitle = $this->locale['calendar_0000'];
        $calendaricon = 'fa fa-question-circle';
        BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $calendarTitle]);

        if ($submissions = dbcount('(submit_id)', DB_SUBMISSIONS, "submit_type='c'")) {
            addNotice("info", sprintf($this->locale['calendar_0064'], format_word($submissions, $this->locale['fmt_submission'])));
        }

        if (!empty($_GET['section'])) {
            switch ($_GET['section']) {
                case "settings":
                    BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $this->locale['calendar_0006']]);
                    break;
                case "submissions":
                    BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $this->locale['calendar_0005']]);
                    break;
                default:
            }

            if ($_GET['section'] == 'calendar') {
                if (isset($_GET['ref'])) {
                    switch ($_GET['ref']) {
                        case 'calendar_form':
                            $calendarTitle = (!empty($_GET['event_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? $this->locale['calendar_0004'] : $this->locale['calendar_0003']);
                            $calendaricon = (!empty($_GET['event_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? 'fa fa-pencil m-r-5' : 'fa fa-plus m-r-5');
                            BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $calendarTitle]);
                            break;
                        case 'calendar_cat_form':
                            $calendarTitle = (!empty($_GET['cat_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? $this->locale['calendar_0008'] : $this->locale['calendar_0007']);
                            $calendaricon = (!empty($_GET['cat_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? 'fa fa-pencil m-r-5' : 'fa fa-plus m-r-5');
                            BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $calendarTitle]);
                            break;
                    }
                }
            }
        }

        // Handle Tabs
        if (!empty($_GET['ref']) || isset($_GET['submit_id'])) {
            $tab['title'][] = $this->locale['back'];
            $tab['id'][] = 'back';
            $tab['icon'][] = 'fa fa-fw fa-arrow-left';
        }

        $tab['title'][] = $calendarTitle;
        $tab['id'][] = 'calendar';
        $tab['icon'][] = $calendaricon;

        $tab['title'][] = $this->locale['calendar_0005'];
        $tab['id'][] = 'submissions';
        $tab['icon'][] = 'fa fa-inbox';

        $tab['title'][] = $this->locale['calendar_0006'];
        $tab['id'][] = 'settings';
        $tab['icon'][] = 'fa fa-cogs';

        // Display Content
        opentable($this->locale['calendar_0000']);
        echo opentab($tab, $_GET['section'], 'calendar_admin', TRUE, '', 'section');
        switch ($_GET['section']) {
            case 'submissions':
                CalendarSubmissionsAdmin::getInstance()->displayCalendarAdmin();
                break;
            case 'settings':
                CalendarSettingsAdmin::getInstance()->displayCalendarAdmin();
                break;
            default:
                CalendarAdmin::getInstance()->displayCalendarAdmin();
        }
        echo closetab();
        closetable();
    }
}
