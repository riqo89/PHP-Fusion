<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss/classes/admin/controllers/rss_settings.inc
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

class RssSettingsAdmin extends RssAdminModel {
    private static $instance = NULL;
    private $locale = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayRssAdmin() {
        pageAccess("RS");
        $this->locale = self::get_rssAdminLocale();
        // Save
        if (!empty($this->save)) {
            $this->SaveRssAdmin();
        }
        $this->RssAdminForm();
    }

    private function SaveRssAdmin() {
        $inputArray = [
            'rss_allow_submission' => form_sanitizer($this->rss_allow_submission, 0, 'rss_allow_submission'),
            'rss_cronjob_refresh' => form_sanitizer($this->rss_cronjob_refresh, 0, 'rss_cronjob_refresh'),
            'rss_cronjob_include' => form_sanitizer($this->rss_cronjob_include, 0, 'rss_cronjob_include')
        ];
        // Update
        if (\defender::safe()) {
            foreach ($inputArray as $settings_name => $settings_value) {
                $inputSettings = [
                    'settings_name'  => $settings_name,
                    'settings_value' => $settings_value,
                    'settings_inf'   => 'rss',
                ];
                dbquery_insert(DB_SETTINGS_INF, $inputSettings, 'update', ['primary_key' => 'settings_name']);
            }
            addNotice('success', $this->locale['900']);
            redirect(FUSION_REQUEST);
        }

        addNotice('danger', $this->locale['901']);
        self::$rss_settings = $inputArray;
    }

    private function RssAdminForm() {
        echo openform('settingsform', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']).
            "<div class='well spacer-xs'>".$this->locale['rss_0400']."</div>\n".
            form_select('rss_allow_submission', $this->locale['rss_0005'], self::$rss_settings['rss_allow_submission'], [
                'inline'  => TRUE,
                'options' => [$this->locale['disable'], $this->locale['enable']]
            ]).
            form_text('rss_cronjob_refresh', $this->locale['rss_0005a'], self::$rss_settings['rss_cronjob_refresh'], [
                'inline'    => TRUE,
                'required'  => TRUE,
                'regex'     => "\d+"
            ]).
            form_select('rss_cronjob_include', $this->locale['rss_0005b'], self::$rss_settings['rss_cronjob_include'], [
                'inline'  => TRUE,
                'options' => [$this->locale['disable'], $this->locale['enable']],
                'ext_tip' => sprintf($this->locale['rss_0005c'], realpath(INFUSIONS."rss/cron.php"))
            ]).
            form_button('savesettings', $this->locale['750'], $this->locale['750'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']).
            closeform();
    }
}
