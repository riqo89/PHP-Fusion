<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss/classes/admin/rss_admin_view.inc
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\RSS;

use PHPFusion\BreadCrumbs;

class RssAdminView extends RssAdminModel {
    private $allowed_pages = ['rss', 'rss_cat', 'rss_cat_form', 'rss_form', 'submissions', 'settings'];
    private $locale = [];

    public function display_admin() {
        $this->locale = self::get_rssAdminLocale();

        // Back and Check Section
        if (isset($_GET['section']) && $_GET['section'] == 'back') {
            redirect(clean_request('', ['ref', 'section', 'action', 'rss_id', 'cat_id', 'submit_id'], FALSE));
        }
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $this->allowed_pages) ? $_GET['section'] : $this->allowed_pages[0];

        // Sitetitle
        add_to_title($this->locale['rss_0000']);

        // Handle Breadcrumbs and Titles
        $rssTitle = $this->locale['rss_0000'];
        $rssicon = 'fa fa-question-circle';
        BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $rssTitle]);

        if ($submissions = dbcount('(submit_id)', DB_SUBMISSIONS, "submit_type='r'")) {
            addNotice("info", sprintf($this->locale['rss_0064'], format_word($submissions, $this->locale['fmt_submission'])));
        }

        if (!empty($_GET['section'])) {
            switch ($_GET['section']) {
                case "settings":
                    BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $this->locale['rss_0006']]);
                    break;
                case "submissions":
                    BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $this->locale['rss_0005']]);
                    break;
                default:
            }

            if ($_GET['section'] == 'rss') {
                if (isset($_GET['ref'])) {
                    switch ($_GET['ref']) {
                        case 'rss_form':
                            $rssTitle = (!empty($_GET['rss_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? $this->locale['rss_0004'] : $this->locale['rss_0003']);
                            $rssicon = (!empty($_GET['rss_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? 'fa fa-pencil m-r-5' : 'fa fa-plus m-r-5');
                            BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $rssTitle]);
                            break;
                        case 'rss_cat_form':
                            $rssTitle = (!empty($_GET['cat_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? $this->locale['rss_0008'] : $this->locale['rss_0007']);
                            $rssicon = (!empty($_GET['cat_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? 'fa fa-pencil m-r-5' : 'fa fa-plus m-r-5');
                            BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $rssTitle]);
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

        $tab['title'][] = $rssTitle;
        $tab['id'][] = 'rss';
        $tab['icon'][] = $rssicon;

        $tab['title'][] = $this->locale['rss_0005'];
        $tab['id'][] = 'submissions';
        $tab['icon'][] = 'fa fa-inbox';

        $tab['title'][] = $this->locale['rss_0006'];
        $tab['id'][] = 'settings';
        $tab['icon'][] = 'fa fa-cogs';

        // Display Content
        opentable($this->locale['rss_0000']);
        echo opentab($tab, $_GET['section'], 'rss_admin', TRUE, '', 'section');
        switch ($_GET['section']) {
            case 'submissions':
                RssSubmissionsAdmin::getInstance()->displayRssAdmin();
                break;
            case 'settings':
                RssSettingsAdmin::getInstance()->displayRssAdmin();
                break;
            default:
                RssAdmin::getInstance()->displayRssAdmin();
        }
        echo closetab();
        closetable();
    }
}
