<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/admin/controllers/calendar_submissions.php
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

class CalendarSubmissionsAdmin extends CalendarAdminModel {
    private static $instance = NULL;
    private static $defArray = [
        'event_breaks'          => 'y',
        'event_visibility'      => 0,
        'event_participation'   => 1,
        'event_status'          => 1,
    ];
    private $locale = [];
    private $inputArray = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Display Admin Area
     */
    public function displayCalendarAdmin() {
        pageAccess("CA");

        $this->locale = self::get_calendarAdminLocale();
        // Handle a Submission
        if (isset($_GET['submit_id']) && isNum($_GET['submit_id']) && dbcount("(submit_id)", DB_SUBMISSIONS, "submit_id=:submitid AND submit_type=:submittype", [':submitid' => $_GET['submit_id'], ':submittype' => 'c'])) {
            $criteria = [
                'criteria'  => ", u.user_id, u.user_name, u.user_status, u.user_avatar",
                'join'      => "LEFT JOIN ".DB_USERS." AS u ON u.user_id=s.submit_user",
                'where'     => 's.submit_type=:submit_type AND s.submit_id=:submit_id',
                'wheredata' => [
                    ':submit_id'   => $_GET['submit_id'],
                    ':submit_type' => 'c'
                ]
            ];
            $data = self::submitData($criteria);
            $data[0] += self::$defArray;
            $data[0] += \defender::decode($data[0]['submit_criteria']);
            $this->inputArray = $data[0];
            // Delete, Publish, Preview

            self::handleDeleteSubmission();
            self::handlePostSubmission();

            // Display Form with Buttons
            self::displayForm();

            // Display List
        } else {
            self::displaySubmissionList();
        }
    }

    private static function submitData(array $filters = []) {
        $query = "SELECT s.*".(!empty($filters['criteria']) ? $filters['criteria'] : "")."
                FROM ".DB_SUBMISSIONS." s
                ".(!empty($filters['join']) ? $filters['join'] : "")."
                WHERE ".(!empty($filters['where']) ? $filters['where'] : "")."
                ORDER BY s.submit_datestamp DESC
                ";

        $result = dbquery($query, $filters['wheredata']);

        $info = [];

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $info[] = $data;
            }
            return $info;
        }
        return FALSE;
    }

    private function handleDeleteSubmission() {
        if (isset($_POST['delete_submission'])) {
            dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submitid AND submit_type=:submittype", [':submitid' => $_GET['submit_id'], ':submittype' => 'c']);
            addNotice('success', $this->locale['calendar_0062']);
            redirect(clean_request('', ['submit_id'], FALSE));
        }
    }

    private function handlePostSubmission() {
        if (isset($_POST['publish_submission']) || isset($_POST['preview_submission'])) {
            // Check posted Informations
            $event_description = "";
            if ($_POST['event_description']) {
                $event_description = stripslashes($_POST['event_description']);
            }

            $SaveinputArray = [
                'event_name'       => form_sanitizer($_POST['event_name'], 0, 'event_name'),
                'event_title'           => form_sanitizer($_POST['event_title'], '', 'event_title'),
                'calendar_cat_id'       => form_sanitizer($_POST['calendar_cat_id'], 0, 'calendar_cat_id'),
                'event_description'     => form_sanitizer($event_description, '', 'event_description'),
                'event_allday'          => isset($_POST['event_allday']) ? 1 : 0,
                'event_start'           => form_sanitizer($_POST['event_start'], '', 'event_start'),
                'event_end'             => form_sanitizer($_POST['event_end'], '', 'event_end'),
                'event_location'        => form_sanitizer($_POST['event_location'], '', 'event_location'),
                'event_datestamp'       => form_sanitizer($_POST['event_datestamp'], TIME, 'event_datestamp'),
                'event_visibility'      => form_sanitizer($_POST['event_visibility'], 0, 'event_visibility'),
                'event_status'          => isset($_POST['event_status']) ? 1 : 0,
                'event_participation'   => isset($_POST['event_participation']) ? 1 : 0,
                'event_language'        => form_sanitizer($_POST['event_language'], LANGUAGE, 'event_language'),
                'event_attachment_url'  => isset($_POST['event_attachment_url']) ? form_sanitizer($_POST['event_attachment_url'], '', 'event_attachment_url') : '',
                'event_attachment_file' => isset($_POST['event_attachment_file']) ? form_sanitizer($_POST['event_attachment_file'], '', 'event_attachment_file') : ''
            ];

            $SaveinputArray['event_start'] = $SaveinputArray['event_allday'] ? strtotime(date("Y-m-d", $SaveinputArray['event_start']))  : $SaveinputArray['event_start'];
            $SaveinputArray['event_end']   = $SaveinputArray['event_allday'] ? strtotime(date("Y-m-d", $SaveinputArray['event_end']))    : $SaveinputArray['event_end'];
            
            $SaveinputArray['event_start'] = Functions::cdateoffset($SaveinputArray['event_start'], FALSE);
            $SaveinputArray['event_end'] = Functions::cdateoffset($SaveinputArray['event_end'], FALSE);

            // Line Breaks
            if (fusion_get_settings("tinymce_enabled") != 1) {
                $SaveinputArray['event_breaks'] = isset($_POST['event_breaks']) ? "y" : "n";
            }

            // Handle
            if (\defender::safe()) {

                // move files
                if (!empty($SaveinputArray['event_attachment_file']) && file_exists(CALENDAR_SUBMISSIONS.$SaveinputArray['event_attachment_file'])) {
                    $temp_file = $SaveinputArray['event_attachment_file'];
                    $SaveinputArray['event_attachment_file'] = filename_exists(CALENDAR_ATTACHMENTS, $SaveinputArray['event_attachment_file']);
                    $SaveinputArray['event_attachment_url'] = '';
                    rename(CALENDAR_SUBMISSIONS.$temp_file, CALENDAR_ATTACHMENTS.$SaveinputArray['event_attachment_file']);
                }

                // Publish Submission
                if (isset($_POST['publish_submission'])) {
                    dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submitid AND submit_type=:submittype", [':submitid' => $_GET['submit_id'], ':submittype' => 'c']);
                    dbquery_insert(DB_CALENDAR_EVENTS, $SaveinputArray, 'save');
                    addNotice('success', ($SaveinputArray['event_status'] ? $this->locale['calendar_0060'] : $this->locale['calendar_0061']));
                    redirect(clean_request('', ['submit_id'], FALSE));
                }

                // Preview Submission
                if (isset($_POST['preview_submission'])) {
                    $footer = openmodal("calendar_preview", "<i class='fa fa-eye fa-lg m-r-10'></i> ".$this->locale['preview'].": ".$SaveinputArray['event_title']);
                    if ($SaveinputArray['event_description']) {
                        $footer .= "<hr class='m-t-20 m-b-20'>\n";
                        $footer .= parse_textarea($SaveinputArray['event_description'], FALSE, FALSE, TRUE, NULL, $SaveinputArray['event_breaks'] == "y");
                    }
                    $footer .= closemodal();
                    add_to_footer($footer);
                }
            }
        }
    }

    /**
     * Display Form
     */
    private function displayForm() {
        // Textarea Settings
        if (!fusion_get_settings("tinymce_enabled")) {
            $calendarExtendedSettings = [
                'required'    => TRUE,
                'preview'     => TRUE,
                'html'        => TRUE,
                'autosize'    => TRUE,
                'placeholder' => $this->locale['calendar_0253'],
                'error_text'  => $this->locale['calendar_0065'],
                'form_name'   => 'calendarform',
                'wordcount'   => TRUE
            ];
        } else {
            $calendarExtendedSettings = [
                'required'   => TRUE,
                'type'       => 'tinymce',
                'tinymce'    => 'advanced',
                'error_text' => $this->locale['calendar_0065']
            ];
        }

        // Start Form
        echo openform('submissionform', 'post', FUSION_REQUEST);
        echo form_hidden('event_name', '', $this->inputArray['user_id']);
        ?>
        <div class="well clearfix m-t-15">
            <div class="pull-left">
                <?php echo display_avatar($this->inputArray, '30px', '', FALSE, 'img-rounded m-t-5 m-r-5'); ?>
            </div>
            <div class="overflow-hide">
                <?php
                $submissionUser = ($this->inputArray['user_name'] != $this->locale['user_na'] ? profile_link($this->inputArray['user_id'], $this->inputArray['user_name'], $this->inputArray['user_status']) : $this->locale['user_na']);
                $submissionDate = showdate("shortdate", $this->inputArray['submit_datestamp']);
                $submissionTime = timer($this->inputArray['submit_datestamp']);

                $replacements = ["{%SUBMISSION_AUTHOR%}" => $submissionUser, "{%SUBMISSION_DATE%}" => $submissionDate, "{%SUBMISSION_TIME%}" => $submissionTime];
                $submissionInfo = strtr($this->locale['calendar_0350']."<br />".$this->locale['calendar_0351'], $replacements);

                echo $submissionInfo;
                ?>
            </div>
        </div>
        <?php self::displayFormButtons('formstart', TRUE); ?>

        <!-- Display Form -->
        <div class="row">

            <!-- Display Left Column -->
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8">
                <?php
                echo form_text('event_title', $this->locale['calendar_0100'], $this->inputArray['event_title'],
                    [
                        'required'   => TRUE,
                        'max_lenght' => 200,
                        'error_text' => $this->locale['calendar_0065']
                    ]);

                echo form_textarea('event_description', $this->locale['calendar_0251'], $this->inputArray['event_description'], $calendarExtendedSettings);

                /* Attachment file input */
                $tab_title['title'][] = $this->locale['calendar_0266'];
                $tab_title['id'][] = 'attf';
                $tab_title['icon'][] = 'fa fa-file-zip-o fa-fw';

                $tab_title['title'][] = $this->locale['calendar_0267'];
                $tab_title['id'][] = 'attl';
                $tab_title['icon'][] = 'fa fa-plug fa-fw';

                $tab_active = tab_active($tab_title, !empty($this->inputArray['event_attachment_url']) ? 1 : 0);

                openside($this->locale['calendar_0268']);
                echo opentab($tab_title, $tab_active, 'attachmenttab');
                echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);

                if (!empty($this->inputArray['event_attachment_file'])) {
                    echo "<div class='m-t-20 m-b-20'>\n";
                    echo $this->locale['calendar_0266']." - <a href='".CALENDAR_SUBMISSIONS.$this->inputArray['event_attachment_file']."'>".CALENDAR_SUBMISSIONS.$this->inputArray['event_attachment_file']."</a>\n";
                    echo form_button('delete_attachment', $this->locale['delete'], $this->inputArray['event_id'], [
                        'class' => 'm-b-0 pull-right btn-danger',
                        'icon'  => 'fa fa-trash fa-fw'
                        ]);
                    echo form_hidden('event_attachment_file', '', $this->inputArray['event_attachment_file']);
                    echo "</div>\n";
                } else {
                    $file_options = [                        
                        "type"        => "object",
                        "class"       => "m-t-10",
                        "width"       => "100%",
                        "upload_path" => CALENDAR_ATTACHMENTS,
                        "max_byte"    => self::$calendar_settings['calendar_attachment_max_b'],
                        "valid_ext"   => self::$calendar_settings['calendar_attachment_types'],
                        "error_text"  => $this->locale['calendar_0269'],
                        "preview_off" => TRUE,
                        "ext_tip"     => sprintf($this->locale['calendar_0270'], parsebytesize(self::$calendar_settings['calendar_attachment_max_b']),
                            str_replace(',', ' ', self::$calendar_settings['calendar_attachment_types']))
                    ];
                    echo form_fileinput('event_attachment_file', $locale['download_0214'], '', $file_options);
                }
                echo closetabbody();

                echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active);
                if (empty($this->inputArray['event_attachment_file'])) {
                    echo form_text('event_attachment_url', $this->locale['calendar_0272'], $this->inputArray['event_attachment_url'], [
                        'type'        => 'url',
                        "class"       => "m-t-10",
                        "inline"      => TRUE,
                        "placeholder" => "http://",
                        "error_text"  => $this->locale['calendar_0273']
                    ]);
                } else {
                    echo "<div>".$this->locale['calendar_0274']."</div>";
                    echo form_hidden('event_attachment_url', '', $this->inputArray['event_attachment_url']);
                }
                echo closetabbody();
                echo closetab();
                closeside();  

                ?>
            </div>

            <!-- Display Right Column -->
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                <?php
                openside($this->locale['calendar_0260']);
                echo form_checkbox('event_allday', $this->locale['calendar_0265'], $this->inputArray['event_allday'], [
                    'class'         => 'm-b-5',
                    'reverse_label' => TRUE
                ]);

                $date_format = [
                    'js'    => $this->inputArray['event_allday'] ? 'DD.MM.YYYY' : 'DD.MM.YYYY H:mm',
                    'php'   => $this->inputArray['event_allday'] ? 'd.m.Y' : 'd.m.Y H:i'
                ];

                echo form_datepicker('event_start', $this->locale['calendar_0261'], Functions::cdateoffset($this->inputArray['event_start']), [
                    'placeholder'       => $this->locale['calendar_0263'],
                    'join_to_id'        => 'event_end',
                    'required'          => TRUE,
                    'showTime'          => TRUE,
                    'date_format_js'    => $date_format['js'],
                    'date_format_php'   => $date_format['php']
                ]);
                echo form_datepicker('event_end', $this->locale['calendar_0262'], Functions::cdateoffset($this->inputArray['event_end']), [
                    'placeholder'       => $this->locale['calendar_0263'],
                    'join_from_id'      => 'event_start',
                    'required'          => TRUE,                    
                    'showTime'          => TRUE,
                    'date_format_js'    => $date_format['js'],
                    'date_format_php'   => $date_format['php']
                ]);
                echo form_text('event_location', $this->locale['calendar_0264'], $this->inputArray['event_location'], [
                    'required'   => FALSE,
                    'max_length' => 200,
                    'error_text' => $this->locale['calendar_0066']
                ]);
                closeside();
                
                openside($this->locale['calendar_0259']);
                $options = [];
                $calendar_result = dbquery("SELECT calendar_cat_id, calendar_cat_name FROM ".DB_CALENDAR_CATS.(multilang_table("CA") ? " WHERE ".in_group('calendar_cat_language', LANGUAGE) : "")." ORDER BY calendar_cat_name ASC");
                if (dbrows($calendar_result)) {
                    $options[0] = $this->locale['calendar_0010'];
                    while ($calendar_data = dbarray($calendar_result)) {
                        $options[$calendar_data['calendar_cat_id']] = $calendar_data['calendar_cat_name'];
                    }
                }
                echo form_select('calendar_cat_id', $this->locale['calendar_0252'], $this->inputArray['calendar_cat_id'],
                    [
                        'inner_width' => '100%',
                        'inline'      => TRUE,
                        'options'     => $options
                    ]);
                echo form_select('event_visibility', $this->locale['calendar_0106'], $this->inputArray['event_visibility'],
                    [
                        'inline'      => TRUE,
                        'placeholder' => $this->locale['choose'],
                        'inner_width' => '100%',
                        'options'     => fusion_get_groups()
                    ]);

                if (multilang_table('CA')) {
                    echo form_select('event_language[]', $this->locale['language'], $this->inputArray['event_language'],
                        [
                            'inline'      => TRUE,
                            'placeholder' => $this->locale['choose'],
                            'inner_width' => '100%',
                            'options'     => fusion_get_enabled_languages(),
                            'multiple'    => TRUE
                        ]);
                } else {
                    echo form_hidden('event_language', "", $this->inputArray['event_language']);
                }

                echo form_datepicker('event_datestamp', $this->locale['calendar_0203'], $this->inputArray['submit_datestamp'],
                    [
                        'inline'      => TRUE,
                        'inner_width' => '100%'
                    ]);

                closeside();

                openside($this->locale['calendar_0259']);

                echo form_checkbox('event_status', $this->locale['calendar_0255'], $this->inputArray['event_status'],
                    [
                        'class'         => 'm-b-5',
                        'reverse_label' => TRUE
                    ]);
                    
                if (self::$calendar_settings['calendar_allow_participation']) {
                    echo form_checkbox('event_participation', $this->locale['calendar_0275'], $this->inputArray['event_participation'], [
                        'class'         => 'm-b-5',
                        'reverse_label' => TRUE
                    ]);
                } else {
                    echo form_hidden('event_participation', '', 0);
                }

                if (fusion_get_settings('tinymce_enabled') != 1) {
                    echo form_checkbox('event_breaks', $this->locale['calendar_0256'], $this->inputArray['event_breaks'],
                        [
                            'value'         => 'y',
                            'class'         => 'm-b-5',
                            'reverse_label' => TRUE
                        ]);
                }

                closeside();
                ?>

            </div>
        </div>
        <?php
        self::displayFormButtons('formend', FALSE);
        echo closeform();
    }

    /**
     *  Display Buttons for Form
     *
     * @param      $unique_id
     * @param bool $breaker
     */
    private function displayFormButtons($unique_id, $breaker = TRUE) {
        ?>
        <div class="m-t-20">
            <?php echo form_button('preview_submission', $this->locale['preview'], $this->locale['preview'], ['class' => 'btn-default', 'icon' => 'fa fa-fw fa-eye', 'input_id' => 'preview_submission-'.$unique_id.'']); ?>
            <?php echo form_button('publish_submission', $this->locale['publish'], $this->locale['publish'], ['class' => 'btn-success m-r-10', 'icon' => 'fa fa-fw fa-hdd-o', 'input_id' => 'publish_submission-'.$unique_id.'']); ?>
            <?php echo form_button('delete_submission', $this->locale['delete'], $this->locale['delete'], ['class' => 'btn-danger', 'icon' => 'fa fa-fw fa-trash', 'input_id' => 'delete_submission-'.$unique_id.'']); ?>
        </div>
        <?php if ($breaker) { ?>
            <hr/><?php } ?>
        <?php
    }

    /**
     * Display List with Submissions
     */
    private function displaySubmissionList() {
        $criteria = [
            'criteria'  => ", u.user_id, u.user_name, u.user_status, u.user_avatar",
            'join'      => "LEFT JOIN ".DB_USERS." AS u ON u.user_id=s.submit_user",
            'where'     => 's.submit_type=:submit_type',
            'wheredata' => [
                ':submit_type' => 'c'
            ]

        ];
        $data = self::submitData($criteria);

        if (!empty($data)) {
            echo "<div class='well m-t-15'>".sprintf($this->locale['calendar_0064'], format_word(count($data), $this->locale['fmt_submission']))."</div>\n";
            echo "<div class='table-responsive m-t-10'><table class='table table-striped'>\n";
            echo "<thead>\n";
            echo "<tr>\n";
            echo "<th class='strong'>".$this->locale['calendar_0200']."</th>\n";
            echo "<th class='strong col-xs-5'>".$this->locale['calendar_0100']."</th>\n";
            echo "<th class='strong'>".$this->locale['calendar_0202']."</th>\n";
            echo "<th class='strong'>".$this->locale['calendar_0203']."</th>\n";
            echo "</tr>\n";
            echo "</thead>\n";
            echo "<tbody>\n";

            foreach ($data as $info) {
                $submitData = \defender::decode($info['submit_criteria']);
                $submitUser = $this->locale['user_na'];
                if ($info['user_name']) {
                    $submitUser = display_avatar($info, '20px', '', TRUE, 'img-rounded m-r-5');
                    $submitUser .= profile_link($info['user_id'], $info['user_name'], $info['user_status']);
                }

                $reviewLink = clean_request('section=submissions&submit_id='.$info['submit_id'], ['section', 'ref', 'action', 'submit_id'], FALSE);

                echo "<tr>\n";
                echo "<td>".$info['submit_id']."</td>\n";
                echo "<td><a href='".$reviewLink."'>".$submitData['event_title']."</a></td>\n";
                echo "<td>".$submitUser."</td>\n";
                echo "<td>".timer($info['submit_datestamp'])."</td>\n";
                echo "</tr>\n";
            }

            echo "</tbody>\n";
            echo "</table>\n</div>";
        } else {
            echo "<div class='well text-center m-t-20'>".$this->locale['calendar_0063']."</div>\n";
        }

    }
}
