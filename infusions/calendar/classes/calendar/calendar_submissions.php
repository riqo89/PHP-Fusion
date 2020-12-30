<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/classes/calendar/calendar_submissions.php
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

class CalendarSubmissions extends CalendarServer {
    private static $instance = NULL;
    public $info = [];
    private $locale = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayCalendar() {
        $this->locale = fusion_get_locale("", CALENDAR_LOCALE);
        add_to_title($this->locale['calendar_0900']);
        $this->info['calendar_tablename'] = $this->locale['calendar_0900'];
        if (iMEMBER && self::$calendar_settings['calendar_allow_submission']) {
            display_calendar_submissions($this->display_submission_form());
        } else {
            $info['no_submissions'] = $this->locale['calendar_0922'];
            $info += $this->info;
            display_calendar_submissions($info);
        }
    }

    private function display_submission_form() {
        $criteriaArray = [
            'event_id'              => 0,
            'calendar_cat_id'       => 0,
            'event_title'           => "",
            'event_description'     => "",
            'event_allday'          => 0,
            'event_start'           => TIME,
            'event_end'             => TIME + 3600,
            'event_location'        => "",
            'event_attachment_file' => "",
            'event_attachment_url'  => "",
            'event_language'        => LANGUAGE,
            'event_status'          => 1
        ];

        if (dbcount("(calendar_cat_id)", DB_CALENDAR_CATS, (multilang_table("CA") ? in_group('calendar_cat_language', LANGUAGE) : ""))) {
            // Save
            if (check_post("submit_link")) {
                $criteriaArray = [
                    'calendar_cat_id'       => form_sanitizer($_POST['calendar_cat_id'], 0, 'calendar_cat_id'),
                    'event_title'           => form_sanitizer(post("event_title"), '', 'event_title'),
                    'event_description'     => form_sanitizer(post("event_description"), '', 'event_description'),
                    'event_allday'          => form_sanitizer(post("event_allday"), '', 'event_allday'),
                    'event_start'           => form_sanitizer(post("event_start"), '', 'event_start'),
                    'event_end'             => form_sanitizer(post("event_end"), '', 'event_end'),
                    'event_location'        => form_sanitizer(post("event_location"), '', 'event_location'),
                    'event_attachment_file' => '',
                    'event_attachment_url'  => '',
                    'event_language'        => form_sanitizer($_POST['event_language'], LANGUAGE, 'event_language'),
                    'event_status'          => 1
                ];

                $criteriaArray['event_start'] = $criteriaArray['event_allday'] ? strtotime(date("Y-m-d", $criteriaArray['event_start']))  : $criteriaArray['event_start'];
                $criteriaArray['event_end']   = $criteriaArray['event_allday'] ? strtotime(date("Y-m-d", $criteriaArray['event_end']))    : $criteriaArray['event_end'];
                
                $criteriaArray['event_start'] = Functions::cdateoffset($criteriaArray['event_start'], FALSE);
                $criteriaArray['event_end'] = Functions::cdateoffset($criteriaArray['event_end'], FALSE);

                $defender_date_format = [
                    'event_start'   => $criteriaArray['event_allday'] ? 'd.m.Y' : 'd.m.Y H:i',
                    'event_end'     => $criteriaArray['event_allday'] ? 'd.m.Y' : 'd.m.Y H:i'
                ];    
    
                foreach($defender_date_format as $input => $date_format) {
                    $_SESSION['form_fields'][\defender::pageHash()][$input]['date_format'] = $date_format;
                }

                // Attachment upload
                if (fusion_safe() && !empty($_FILES['event_attachment_file']['name']) && is_uploaded_file($_FILES['event_attachment_file']['tmp_name'])) {
                    $upload = form_sanitizer($_FILES['event_attachment_file'], '', 'event_attachment_file');
                    if (empty($upload['error'])) {
                        $criteriaArray['event_attachment_file'] = !empty($upload['target_file']) ? $upload['target_file'] : $upload['name'];
                    }
                } else if (!empty($_POST['event_attachment_url']) && empty($data['event_attachment_file'])) {
                    $criteriaArray['event_attachment_url'] = form_sanitizer($_POST['event_attachment_url'], '', 'event_attachment_url');
                    $criteriaArray['event_attachment_file'] = '';
                }

                // Save
                if (fusion_safe()) {
                    $inputArray = [
                        'submit_type'      => 'c',
                        'submit_user'      => fusion_get_userdata('user_id'),
                        'submit_datestamp' => TIME,
                        'submit_criteria'  => \Defender::encode($criteriaArray)
                    ];
                    dbquery_insert(DB_SUBMISSIONS, $inputArray, 'save');
                    addNotice('success', $this->locale['calendar_0910']);
                    redirect(clean_request('submitted=c', ['stype'], TRUE));
                }
            }

            if (get("submitted") === "q") {
                $info['confirm'] = [
                    'title'       => $this->locale['calendar_0911'],
                    'submit_link' => "<a href='".BASEDIR."submit.php?stype=c'>".$this->locale['calendar_0912']."</a>",
                    'index_link'  => "<a href='".BASEDIR."index.php'>".str_replace("[SITENAME]", fusion_get_settings("sitename"), $this->locale['calendar_0913'])."</a>"
                ];
                $info += $this->info;
                return (array)$info;
            } else {
                $options = [];
                $calendar_result = dbquery("SELECT calendar_cat_id, calendar_cat_name FROM ".DB_CALENDAR_CATS.(multilang_table("CA") ? " WHERE ".in_group('calendar_cat_language', LANGUAGE) : "")." ORDER BY calendar_cat_name ASC");
                if (dbrows($calendar_result)) {
                    $options[0] = $this->locale['calendar_0010'];
                    while ($calendar_data = dbarray($calendar_result)) {
                        $options[$calendar_data['calendar_cat_id']] = $calendar_data['calendar_cat_name'];
                    }
                }

                $date_format = [
                    'js'    => $this->calendar_data['event_allday'] ? 'DD.MM.YYYY' : 'DD.MM.YYYY H:mm',
                    'php'   => $this->calendar_data['event_allday'] ? 'd.m.Y' : 'd.m.Y H:i'
                ];

                /* Attachment file input */
                $tab_title['title'][] = $this->locale['calendar_0266'];
                $tab_title['id'][] = 'attf';
                $tab_title['icon'][] = 'fa fa-file-zip-o fa-fw';

                $tab_title['title'][] = $this->locale['calendar_0267'];
                $tab_title['id'][] = 'attl';
                $tab_title['icon'][] = 'fa fa-plug fa-fw';
                $tab_active = tab_active($tab_title, 0);

                $attachment = opentab($tab_title, $tab_active, 'attachmenttab');
                $attachment .= opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);
                $attachment .= form_fileinput('event_attachment_file', $locale['download_0214'], '', [                        
                    "type"        => "object",
                    "class"       => "m-t-10",
                    "width"       => "100%",
                    "upload_path" => CALENDAR_SUBMISSIONS,
                    "max_byte"    => self::$calendar_settings['calendar_attachment_max_b'],
                    "valid_ext"   => self::$calendar_settings['calendar_attachment_types'],
                    "error_text"  => $this->locale['calendar_0269'],
                    "preview_off" => TRUE,
                    "ext_tip"     => sprintf($this->locale['calendar_0270'], parsebytesize(self::$calendar_settings['calendar_attachment_max_b']),
                        str_replace(',', ' ', self::$calendar_settings['calendar_attachment_types']))
                ]);
                $attachment .= closetabbody();             
                $attachment .= opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active);
                $attachment .= form_text('event_attachment_url', $this->locale['calendar_0272'], '', [
                    'type'        => 'url',
                    "class"       => "m-t-10",
                    "inline"      => TRUE,
                    "placeholder" => "http://",
                    "error_text"  => $this->locale['calendar_0273']
                ]);                
                $attachment .= closetabbody();
                $attachment .= closetab();

                $info['item'] = [
                    'guidelines'            => str_replace("[SITENAME]", fusion_get_settings("sitename"), $this->locale['calendar_0920']),
                    'openform'              => openform('submit_form', 'post', BASEDIR."submit.php?stype=c", ['enctype' => self::$calendar_settings['calendar_allow_submission'] ? TRUE : FALSE]),
                    'event_title'           => form_text('event_title', $this->locale['calendar_0100'], $criteriaArray['event_title'],
                        [
                            'error_text'        => $this->locale['calendar_0066'],
                            'required'          => TRUE
                        ]),
                    'event_description'     => form_textarea('event_description', $this->locale['calendar_0251'], $criteriaArray['event_description'],
                        [
                            'required'          => TRUE,
                            'type'              => fusion_get_settings('tinymce_enabled') ? 'tinymce' : 'html',
                            'tinymce'           => fusion_get_settings('tinymce_enabled') && iADMIN ? 'advanced' : 'simple',
                            'tinymce_image'     => FALSE,
                            'autosize'          => TRUE,
                            'form_name'         => 'submit_form'
                        ]),
                    'event_allday'          => form_checkbox('event_allday', $this->locale['calendar_0265'], $criteriaArray['event_allday'],
                        [
                            'class'             => 'm-b-5',
                            'reverse_label'     => TRUE
                        ]),
                    'event_start'           => form_datepicker('event_start', $this->locale['calendar_0261'], Functions::cdateoffset($criteriaArray['event_start']),
                        [
                            'placeholder'       => $this->locale['calendar_0263'],
                            'join_to_id'        => 'event_end',
                            'required'          => TRUE,
                            'showTime'          => TRUE,
                            'date_format_js'    => $date_format['js'],
                            'date_format_php'   => $date_format['php']
                        ]),
                    'event_end'             => form_datepicker('event_end', $this->locale['calendar_0262'], Functions::cdateoffset($criteriaArray['event_end']),
                        [
                            'placeholder'       => $this->locale['calendar_0263'],
                            'join_from_id'      => 'event_start',
                            'required'          => TRUE,                    
                            'showTime'          => TRUE,
                            'date_format_js'    => $date_format['js'],
                            'date_format_php'   => $date_format['php']
                        ]),                 
                    'event_location'        => form_text('event_location', $this->locale['calendar_0264'], $criteriaArray['event_location'],
                        [
                            'required'          => FALSE,
                            'max_length'        => 200,
                            'error_text'        => $this->locale['calendar_0066']
                        ]),                 
                    'event_attachment'      => $attachment,   
                    'calendar_cat_id'       => form_select('calendar_cat_id', $this->locale['calendar_0252'], $criteriaArray['calendar_cat_id'],
                        [
                            'inner_width'       => '250px',
                            'inline'            => TRUE,
                            'options'           => $options
                        ]),
                    'event_language'        => (multilang_table('CA') ? form_select('event_language[]', $this->locale['language'], $criteriaArray['event_language'],
                        [
                            'options'           => fusion_get_enabled_languages(),
                            'placeholder'       => $this->locale['choose'],
                            'width'             => '250px',
                            'inline'            => TRUE,
                            'multiple'          => TRUE
                        ]) : form_hidden('event_language', '', $criteriaArray['event_language'])),
                    'calendar_submit'       => form_button('submit_link', $this->locale['submit'], $this->locale['submit'], ['class' => 'btn-success', 'icon' => 'fa fa-fw fa-hdd-o']),
                    'criteria_array'        => $criteriaArray

                ];
                $info += $this->info;
                return (array)$info;
            }
        } else {
            $info['no_submissions'] = $this->locale['calendar_0923'];
            $info += $this->info;
            return (array)$info;
        }
    }
}
