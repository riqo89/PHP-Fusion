<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/classes/admin/controllers/calendar_settings.php
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

class CalendarSettingsAdmin extends CalendarAdminModel {
    private static $instance = NULL;
    private $locale = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayCalendarAdmin() {
        pageAccess("CA");
        $this->locale = self::get_calendarAdminLocale();
        // Save
        if (!empty($this->save)) {
            $this->SaveCalendarAdmin();
        }
        $this->CalendarAdminForm();
    }

    private function SaveCalendarAdmin() {
        $inputArray = [
            'calendar_pagination'               => form_sanitizer($_POST['calendar_pagination'], 15, 'calendar_pagination'),
            'calendar_pagination_archive'       => form_sanitizer($_POST['calendar_pagination_archive'], 50, 'calendar_pagination_archive'),
            'calendar_allow_submission'         => form_sanitizer($_POST['calendar_allow_submission'], 0, 'calendar_allow_submission'),
            'calendar_allow_participation'      => form_sanitizer($_POST['calendar_allow_participation'], 0, 'calendar_allow_participation'),
            'calendar_allow_attachments'        => form_sanitizer($_POST['calendar_allow_attachments'], 0, 'calendar_allow_attachments'),
            'calendar_attachment_max_b'         => form_sanitizer($_POST['calc_b'], 153600, "calc_b") * form_sanitizer($_POST['calc_c'], 1, "calc_c"),
            'calendar_attachment_types'         => form_sanitizer($_POST['calendar_attachment_types'], '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'calendar_attachment_types'),
            'calendar_format_shortdate'         => form_sanitizer($_POST['calendar_format_shortdate'], '%d.%m.%Y', 'calendar_format_shortdate'),
            'calendar_format_longdate'          => form_sanitizer($_POST['calendar_format_longdate'], '%d. %B %Y', 'calendar_format_longdate'),
            'calendar_format_titledate_day'     => form_sanitizer($_POST['calendar_format_titledate_day'], '%A, %d. %B %Y', 'calendar_format_titledate_day'),
            'calendar_format_titledate_month'   => form_sanitizer($_POST['calendar_format_titledate_month'], '%B %Y', 'calendar_format_titledate_month'),
            'calendar_format_titledate_year'    => form_sanitizer($_POST['calendar_format_titledate_year'], '%Y', 'calendar_format_titledate_year'),
            'calendar_format_shorttime'         => form_sanitizer($_POST['calendar_format_shorttime'], '%R', 'calendar_format_shorttime'),
            'calendar_format_longtime'          => form_sanitizer($_POST['calendar_format_longtime'], '%T', 'calendar_format_longtime'),
            'calendar_show_gmaps_iframe'        => form_sanitizer($_POST['calendar_show_gmaps_iframe'], 0, 'calendar_show_gmaps_iframe')            
        ];
        // Update
        if (\defender::safe()) {
            foreach ($inputArray as $settings_name => $settings_value) {
                $inputSettings = [
                    'settings_name'  => $settings_name,
                    'settings_value' => $settings_value,
                    'settings_inf'   => 'calendar',
                ];
                dbquery_insert(DB_SETTINGS_INF, $inputSettings, 'update', ['primary_key' => 'settings_name']);
            }
            addNotice('success', $this->locale['900']);
            redirect(FUSION_REQUEST);
        }

        addNotice('danger', $this->locale['901']);
        self::$calendar_settings = $inputArray;
    }

    private function CalendarAdminForm() {

        $date_opts = [];
        $dateformats = fusion_get_locale('calendar_dateformats', CALENDAR.'locale/formats.php');
        foreach ($dateformats as $dateformat) {
            $date_opts[$dateformat] = showdate($dateformat, TIME);
        }

        $time_opts = [];
        $timeformats = fusion_get_locale('calendar_timeformats', CALENDAR.'locale/formats.php');
        foreach ($timeformats as $timeformat) {
            $time_opts[$timeformat] = showdate($timeformat, TIME);
        }
        
        $mime_opts = [];
        $mime = mimeTypes();
        foreach ($mime as $m => $Mime) {
            $ext = ".$m";
            $mime_opts[$ext] = $ext;
        }

        $calc_opts = $this->locale['1020'];
        $calc_c = calculate_byte(self::$calendar_settings['calendar_attachment_max_b']);
        $calc_b = self::$calendar_settings['calendar_attachment_max_b'] / $calc_c;

        echo openform('settingsform', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);
        echo "<div class='well spacer-xs'>".$this->locale['calendar_0400']."</div>\n";

        echo openside($this->locale['calendar_0401']);
        echo form_text('calendar_pagination', $this->locale['calendar_0402'], self::$calendar_settings['calendar_pagination'], [
                'inline'      => TRUE,
                'max_length'  => 4,
                'inner_width' => '250px',
                'width'       => '150px',
                'type'        => 'number'
            ]);
        echo form_text('calendar_pagination_archive', $this->locale['calendar_0421'], self::$calendar_settings['calendar_pagination_archive'], [
            'inline'      => TRUE,
            'max_length'  => 4,
            'inner_width' => '250px',
            'width'       => '150px',
            'type'        => 'number'
        ]);
        echo form_select('calendar_allow_submission', $this->locale['calendar_0403'], self::$calendar_settings['calendar_allow_submission'], [
                'inline'  => TRUE,
                'options' => [$this->locale['disable'], $this->locale['enable']]
            ]);
        echo form_select('calendar_allow_participation', $this->locale['calendar_0404'], self::$calendar_settings['calendar_allow_participation'], [
            'inline'  => TRUE,
            'options' => [$this->locale['disable'], $this->locale['enable']]
        ]);
        echo closeside();

        echo openside($this->locale['calendar_0405']);
        echo form_select('calendar_allow_attachments', $this->locale['calendar_0406'], self::$calendar_settings['calendar_allow_attachments'], [
                'inline'  => TRUE,
                'options' => [$this->locale['disable'], $this->locale['enable']]
            ]);

        echo "<div class='row'>
            <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='calc_bb'>".$this->locale['calendar_0407']."</label>
            <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>";
        echo form_text('calc_b', '', $calc_b, [
                'required'   => TRUE,
                'type'       => 'number',
                'error_text' => $this->locale['error_rate'],
                'width'      => '100px',
                'max_length' => 4,
                'class'      => 'pull-left m-r-10'
            ]);
        echo form_select('calc_c', '', $calc_c, [
                'options'     => $calc_opts,
                'placeholder' => $this->locale['choose'],
                'class'       => 'pull-left',
                'inner_width' => '100%',
                'width'       => '180px'
            ]);
        echo "</div></div>";

        echo form_select('calendar_attachment_types[]', $this->locale['calendar_0408'], self::$calendar_settings['calendar_attachment_types'], [
            'options'     => $mime_opts,
            'inline'      => TRUE,
            'input_id'    => 'attype',
            'error_text'  => $this->locale['error_type'],
            'placeholder' => $this->locale['choose'],
            'multiple'    => TRUE,
            'tags'        => TRUE,
            'width'       => '100%'
            ]);
        echo closeside();

        echo openside($this->locale['calendar_0409']);
        echo form_select('calendar_format_shortdate', $this->locale['calendar_0410'], self::$calendar_settings['calendar_format_shortdate'], [
            'inline'  => TRUE,
            'options'     => $date_opts
        ]);
        echo form_select('calendar_format_longdate', $this->locale['calendar_0411'], self::$calendar_settings['calendar_format_longdate'], [
            'inline'  => TRUE,
            'options'     => $date_opts
        ]);
        echo form_select('calendar_format_titledate_day', $this->locale['calendar_0413'], self::$calendar_settings['calendar_format_titledate_day'], [
            'inline'  => TRUE,
            'options'     => $date_opts
        ]);
        echo form_select('calendar_format_titledate_month', $this->locale['calendar_0414'], self::$calendar_settings['calendar_format_titledate_month'], [
            'inline'  => TRUE,
            'options'     => $date_opts
        ]);
        echo form_select('calendar_format_titledate_year', $this->locale['calendar_0415'], self::$calendar_settings['calendar_format_titledate_year'], [
            'inline'  => TRUE,
            'options'     => $date_opts
        ]);
        echo closeside();
        
        echo openside($this->locale['calendar_0416']);
        echo form_select('calendar_format_shorttime', $this->locale['calendar_0417'], self::$calendar_settings['calendar_format_shorttime'], [
            'inline'  => TRUE,
            'options'     => $time_opts
        ]);
        echo form_select('calendar_format_longtime', $this->locale['calendar_0418'], self::$calendar_settings['calendar_format_longtime'], [
            'inline'  => TRUE,
            'options'     => $time_opts
        ]);
        echo closeside();

        echo openside($this->locale['calendar_0419']);  
        echo form_select('calendar_show_gmaps_iframe', $this->locale['calendar_0420'], self::$calendar_settings['calendar_show_gmaps_iframe'], [
            'inline'  => TRUE,
            'options' => [$this->locale['disable'], $this->locale['enable']]
        ]);
        echo closeside();
        echo form_button('savesettings', $this->locale['750'], $this->locale['750'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']);
           
        echo closeform();
    }
}
