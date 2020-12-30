<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: calendar/admin/controllers/calendar.php
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

class CalendarAdmin extends CalendarAdminModel {
    private static $instance = NULL;
    private $locale = [];
    private $calendar_data = [];
    private $form_action = FUSION_REQUEST;
    private $cat_data = [
        'calendar_cat_id'          => 0,
        'calendar_cat_name'        => '',
        'calendar_cat_description' => '',
        'calendar_cat_language'    => LANGUAGE,
    ];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayCalendarAdmin() {
        pageAccess('CA');
        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
        $this->locale = self::get_calendarAdminLocale();
        if (isset($_GET['ref'])) {
            switch ($_GET['ref']) {
                case 'calendar_cat_form':
                    $this->display_calendar_category_form();
                    break;
                case 'calendar_form':
                    $this->display_calendar_form();
                    break;
                default:
                    $this->display_calendar_listing();
            }
        } else {
            $this->display_calendar_listing();
        }

    }

    private function display_calendar_category_form() {
        if (isset($_POST['save_cat'])) {
            $this->cat_data = [
                'calendar_cat_id'          => form_sanitizer($_POST['calendar_cat_id'], '', 'calendar_cat_id'),
                'calendar_cat_name'        => form_sanitizer($_POST['calendar_cat_name'], '', 'calendar_cat_name'),
                'calendar_cat_description' => form_sanitizer($_POST['calendar_cat_description'], '', 'calendar_cat_description'),
                'calendar_cat_language'    => form_sanitizer($_POST['calendar_cat_language'], LANGUAGE, 'calendar_cat_language'),
            ];

            if (\defender::safe()) {
                if ($this->cat_data['calendar_cat_id']) {
                    dbquery_insert(DB_CALENDAR_CATS, $this->cat_data, 'update');
                    addNotice('success', $this->locale['calendar_0040']);
                } else {
                    if (!dbcount("(calendar_cat_id)", DB_CALENDAR_CATS, "calendar_cat_name=:calendar_cat_name", [':calendar_cat_name' => $this->cat_data['calendar_cat_name']])) {
                        dbquery_insert(DB_CALENDAR_CATS, $this->cat_data, 'save');
                        addNotice('success', $this->locale['calendar_0039']);
                    } else {
                        \defender::stop();
                        \defender::inputHasError('calendar_cat_name');
                        addNotice('warning', $this->locale['calendar_0042']);
                    }
                }
                redirect(clean_request('', ['ref', 'cat_id', 'action'], FALSE));
            }

        }

        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) && isset($_GET['action']) && $_GET['action'] == 'edit') {
            $result = dbquery("SELECT * FROM ".DB_CALENDAR_CATS." WHERE calendar_cat_id=:calendar_cat_id", [':calendar_cat_id' => $_GET['cat_id']]);
            if (dbrows($result) > 0) {
                $this->cat_data = dbarray($result);
            } else {
                redirect(clean_request('', ['ref'], FALSE));
            }
        }
        echo openform('add_calendar_cat', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);
        echo form_hidden('calendar_cat_id', '', $this->cat_data['calendar_cat_id']);
        echo form_text('calendar_cat_name', $this->locale['calendar_0115'], $this->cat_data['calendar_cat_name'], ['error_text' => $this->locale['460'], 'required' => TRUE, 'inline' => TRUE]);
        echo form_textarea('calendar_cat_description', $this->locale['calendar_0116'], $this->cat_data['calendar_cat_description'], ['autosize' => TRUE, 'inline' => TRUE]);
        if (multilang_table("CA")) {
            echo form_select('calendar_cat_language[]', $this->locale['calendar_0117'], $this->cat_data['calendar_cat_language'], [
                'options'     => fusion_get_enabled_languages(),
                'inline'      => TRUE,
                'placeholder' => $this->locale['choose'],
                'multiple'    => TRUE
            ]);
        } else {
            echo form_hidden('cat_language', '', LANGUAGE);
        }
        echo form_button('save_cat', $this->locale['calendar_0118'], $this->locale['calendar_0118'], ['class' => 'btn-primary m-t-10']);
        echo closeform();
    }

    /**
     * Displays Calendar Form
     */
    private function display_calendar_form() {
        // Delete
        self::execute_Delete();

        // Update
        self::execute_Update();

        /**
         * Global vars
         */
        if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['event_id']) && isnum($_POST['event_id'])) || (isset($_GET['event_id']) && isnum($_GET['event_id']))) {
            $id = (!empty($_POST['event_id']) ? $_POST['event_id'] : $_GET['event_id']);
            $criteria = [
                'criteria' => "ac.*, u.user_id, u.user_name, u.user_status, u.user_avatar",
                'join'     => "LEFT JOIN ".DB_USERS." AS u ON u.user_id=ac.event_name",
                'where'    => "ac.event_id='$id'".(multilang_table("CA") ? " AND ".in_group('ac.event_language', LANGUAGE) : ""),

            ];
            $result = self::CalendarData($criteria);
            if (dbrows($result) > 0) {
                $this->calendar_data = dbarray($result);
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        } else {
            $this->calendar_data = $this->default_data;
            $this->calendar_data['event_breaks'] = (fusion_get_settings('tinymce_enabled') ? 'n' : 'y');
        }
        self::calendarContent_form();
    }

    private function execute_Delete() {
        if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['event_id']) && isnum($_GET['event_id'])) {
            $event_id = intval($_GET['event_id']);
            $result = dbquery("SELECT * FROM ".DB_CALENDAR_EVENTS." WHERE event_id='".$event_id."'");
            if (dbrows($result) > 0) {
                $data = dbarray($result);
                if (!empty($data['event_attachment_file']) && file_exists(CALENDAR_ATTACHMENTS.$data['event_attachment_file'])) {
                    @unlink(CALENDAR_ATTACHMENTS.$data['event_attachment_file']);
                }
                dbquery("DELETE FROM  ".DB_CALENDAR_EVENTS." WHERE event_id=:eventid", [':eventid' => intval($event_id)]);                
                addNotice('success', $this->locale['calendar_0032']);
            } else {
                addNotice('warning', $this->locale['calendar_0035']);
            }
            redirect(clean_request('', ['ref', 'action', 'cat_id'], FALSE));
        }

        /* Delete File */
        if (isset($_POST['delete_attachment']) && isnum($_POST['delete_attachment'])) {
            $result = dbquery("SELECT * FROM ".DB_CALENDAR_EVENTS." WHERE event_id='".intval($_POST['delete_attachment'])."'");
            if (dbrows($result) > 0) {
                $data = dbarray($result);
                if (!empty($data['event_attachment_file']) && file_exists(CALENDAR_ATTACHMENTS.$data['event_attachment_file'])) {
                    @unlink(CALENDAR_ATTACHMENTS.$data['event_attachment_file']);
                }
                $data['event_attachment_file'] = '';
                dbquery_insert(DB_CALENDAR_EVENTS, $data, 'update');
                redirect(FUSION_REQUEST);
            }
        }
    }

    /**
     * Create or Update
     */
    private function execute_Update() {
        if ((isset($_POST['save'])) or (isset($_POST['save_and_close']))) {

            // Check posted Informations
            $event_description = '';
            if ($_POST['event_description']) {
                $event_description = fusion_get_settings("allow_php_exe") ? htmlspecialchars($_POST['event_description']) : stripslashes($_POST['event_description']);
            }
            
            $defender_date_format = [
                'event_start'   => isset($_POST['event_allday']) ? 'd.m.Y' : 'd.m.Y H:i',
                'event_end'     => isset($_POST['event_allday']) ? 'd.m.Y' : 'd.m.Y H:i'
            ];


            foreach($defender_date_format as $input => $date_format) {
                $_SESSION['form_fields'][\defender::pageHash()][$input]['date_format'] = $date_format;
            }
            
            $this->calendar_data = [
                'event_id'              => form_sanitizer($_POST['event_id'], 0, 'event_id'),
                'event_title'           => form_sanitizer($_POST['event_title'], '', 'event_title'),
                'calendar_cat_id'       => form_sanitizer($_POST['calendar_cat_id'], 0, 'calendar_cat_id'),
                'event_description'     => form_sanitizer($event_description, '', 'event_description'),
                'event_allday'          => isset($_POST['event_allday']) ? 1 : 0,
                'event_start'           => form_sanitizer($_POST['event_start'], '', 'event_start'),
                'event_end'             => form_sanitizer($_POST['event_end'], '', 'event_end'),
                'event_location'        => form_sanitizer($_POST['event_location'], '', 'event_location'),
                'event_datestamp'       => form_sanitizer($_POST['event_datestamp'], '', 'event_datestamp'),
                'event_visibility'      => form_sanitizer($_POST['event_visibility'], 0, 'event_visibility'),
                'event_status'          => isset($_POST['event_status']) ? 1 : 0,
                'event_participation'   => isset($_POST['event_participation']) ? 1 : 0,
                'event_language'        => form_sanitizer($_POST['event_language'], LANGUAGE, 'event_language'),
                'event_attachment_url'  => '',
                'event_attachment_file' => isset($_POST['event_attachment_file']) ? form_sanitizer($_POST['event_attachment_file'], '', 'event_attachment_file') : '',
            ];

            $this->calendar_data['event_start'] = $this->calendar_data['event_allday'] ? strtotime(date("Y-m-d", $this->calendar_data['event_start']))  : $this->calendar_data['event_start'];
            $this->calendar_data['event_end']   = $this->calendar_data['event_allday'] ? strtotime(date("Y-m-d", $this->calendar_data['event_end']))    : $this->calendar_data['event_end'];

            $this->calendar_data['event_start'] = Functions::cdateoffset($this->calendar_data['event_start'], FALSE);
            $this->calendar_data['event_end'] = Functions::cdateoffset($this->calendar_data['event_end'], FALSE);

            // Line Breaks
            if (fusion_get_settings('tinymce_enabled') != 1) {
                $this->calendar_data['event_breaks'] = isset($_POST['event_breaks']) ? "y" : "n";
            } else {
                $this->calendar_data['event_breaks'] = "n";
            }
            
            // Attachment upload
            if (\defender::safe() && !empty($_FILES['event_attachment_file']['name']) && is_uploaded_file($_FILES['event_attachment_file']['tmp_name'])) {
                $upload = form_sanitizer($_FILES['event_attachment_file'], '', 'event_attachment_file');
                if (empty($upload['error'])) {
                    $this->calendar_data['event_attachment_file'] = !empty($upload['target_file']) ? $upload['target_file'] : $upload['name'];
                }
            } else if (!empty($_POST['event_attachment_url']) && empty($data['event_attachment_file'])) {
                $this->calendar_data['event_attachment_url'] = form_sanitizer($_POST['event_attachment_url'], '', 'event_attachment_url');
                $this->calendar_data['event_attachment_file'] = '';
            }

            // Handle
            if (\defender::safe()) {

                // Update
                if (dbcount("(event_id)", DB_CALENDAR_EVENTS, "event_id='".$this->calendar_data['event_id']."'")) {
                    $this->calendar_data['event_datestamp'] = isset($_POST['update_datestamp']) ? time() : $this->calendar_data['event_datestamp'];
                    dbquery_insert(DB_CALENDAR_EVENTS, $this->calendar_data, 'update');
                    addNotice('success', $this->locale['calendar_0031']);

                    // Create
                } else {
                    $this->calendar_data['event_name'] = fusion_get_userdata('user_id');
                    $this->calendar_data['event_id'] = dbquery_insert(DB_CALENDAR_EVENTS, $this->calendar_data, 'save');
                    addNotice('success', $this->locale['calendar_0030']);
                }

                // Redirect
                if (isset($_POST['save_and_close'])) {
                    redirect(clean_request('', ['ref', 'action', 'event_id'], FALSE));
                } else {
                    redirect(FUSION_REQUEST);
                    
                }
            }
        }
        
    }

    private static function CalendarData(array $filters = []) {

        $result = dbquery("SELECT ".(!empty($filters['criteria']) ? $filters['criteria'] : "")."
            FROM ".DB_CALENDAR_EVENTS." ac
            ".(!empty($filters['join']) ? $filters['join'] : "")."
            WHERE ".(!empty($filters['where']) ? $filters['where'] : "").
            (!empty($filters['sql_condition']) ? $filters['sql_condition'] : "")."
            GROUP BY ac.event_id
            ORDER BY ac.event_datestamp DESC
            ".(!empty($filters['limit']) ? $filters['limit'] : "")."
        ");

        return $result;
    }

    /**
     * Display Form
     */
    private function calendarContent_form() {

        
        // Textarea Settings
        if (!fusion_get_settings("tinymce_enabled")) {
            $calendarExtendedSettings = [
                'required'    => FALSE,
                'preview'     => TRUE,
                'html'        => TRUE,
                'autosize'    => TRUE,
                'placeholder' => $this->locale['calendar_0253'],
                'error_text'  => $this->locale['calendar_0066'],
                'form_name'   => 'calendarform',
                'wordcount'   => TRUE
            ];
        } else {
            $calendarExtendedSettings = [
                'required'   => FALSE,
                'type'       => 'tinymce',
                'tinymce'    => 'advanced',
                'error_text' => $this->locale['calendar_0066']
            ];
        }

        add_to_jquery("
            $('#event_allday').click(function() {
                if ($(this).is(':checked')) {
                    $('#event_start-datepicker').datetimepicker().data('DateTimePicker').format('DD.MM.YYYY');  
                    $('#event_end-datepicker').datetimepicker().data('DateTimePicker').format('DD.MM.YYYY');
                } else if ($(this).is(':not(:checked)')) {
                    $('#event_start-datepicker').datetimepicker().data('DateTimePicker').format('DD.MM.YYYY H:mm');  
                    $('#event_end-datepicker').datetimepicker().data('DateTimePicker').format('DD.MM.YYYY H:mm');           
                }                
            });
        ");        

        // Start Form
        echo openform('inputform', 'post', $this->form_action, ['enctype' => 1, 'class' => 'spacer-sm']);
        echo form_hidden('event_id', '', $this->calendar_data['event_id']);
        ?>

        <!-- Display Form -->
        <div class='row'>
            <!-- Display Left Column -->
            <div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>
                <?php
                echo form_text('event_title', '', $this->calendar_data['event_title'], [
                    'required'   => TRUE,
                    'max_length' => 200,
                    'error_text' => $this->locale['calendar_0066'],
                    'class'       => 'form-group-lg',
                    'placeholder' => $this->locale['calendar_0100']
                ]);
                echo form_textarea('event_description', $this->locale['calendar_0251'], $this->calendar_data['event_description'], $calendarExtendedSettings);

                /* Attachment file input */
                $tab_title['title'][] = $this->locale['calendar_0266'];
                $tab_title['id'][] = 'attf';
                $tab_title['icon'][] = 'fa fa-file-zip-o fa-fw';

                $tab_title['title'][] = $this->locale['calendar_0267'];
                $tab_title['id'][] = 'attl';
                $tab_title['icon'][] = 'fa fa-plug fa-fw';

                $tab_active = tab_active($tab_title, !empty($this->calendar_data['event_attachment_url']) ? 1 : 0);

                openside($this->locale['calendar_0268']);
                echo opentab($tab_title, $tab_active, 'attachmenttab');
                echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);

                if (!empty($this->calendar_data['event_attachment_file'])) {
                    echo "<div class='m-t-20 m-b-20'>\n";
                    echo $this->locale['calendar_0266']." - <a href='".CALENDAR_ATTACHMENTS.$this->calendar_data['event_attachment_file']."'>".CALENDAR_ATTACHMENTS.$this->calendar_data['event_attachment_file']."</a>\n";
                    echo form_button('delete_attachment', $this->locale['delete'], $this->calendar_data['event_id'], [
                        'class' => 'm-b-0 pull-right btn-danger',
                        'icon'  => 'fa fa-trash fa-fw'
                        ]);
                    echo form_hidden('event_attachment_file', '', $this->calendar_data['event_attachment_file']);
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
                if (empty($this->calendar_data['event_attachment_file'])) {
                    echo form_text('event_attachment_url', $this->locale['calendar_0272'], $this->calendar_data['event_attachment_url'], [
                        'type'        => 'url',
                        "class"       => "m-t-10",
                        "inline"      => TRUE,
                        "placeholder" => "http://",
                        "error_text"  => $this->locale['calendar_0273']
                    ]);
                } else {
                    echo "<div>".$this->locale['calendar_0274']."</div>";
                    echo form_hidden('event_attachment_url', '', $this->calendar_data['event_attachment_url']);
                }
                echo closetabbody();
                echo closetab();
                closeside();  
                ?>
            </div>
            <!-- Display Right Column -->
            <div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>
                <?php
                openside($this->locale['calendar_0260']);
                echo form_checkbox('event_allday', $this->locale['calendar_0265'], $this->calendar_data['event_allday'], [
                    'class'         => 'm-b-5',
                    'reverse_label' => TRUE
                ]);

                $date_format = [
                    'js'    => $this->calendar_data['event_allday'] ? 'DD.MM.YYYY' : 'DD.MM.YYYY H:mm',
                    'php'   => $this->calendar_data['event_allday'] ? 'd.m.Y' : 'd.m.Y H:i'
                ];

                echo form_datepicker('event_start', $this->locale['calendar_0261'], Functions::cdateoffset($this->calendar_data['event_start']), [
                    'placeholder'       => $this->locale['calendar_0263'],
                    'join_to_id'        => 'event_end',
                    'required'          => TRUE,
                    'showTime'          => TRUE,
                    'date_format_js'    => $date_format['js'],
                    'date_format_php'   => $date_format['php']
                ]);
                echo form_datepicker('event_end', $this->locale['calendar_0262'], Functions::cdateoffset($this->calendar_data['event_end']), [
                    'placeholder'       => $this->locale['calendar_0263'],
                    'join_from_id'      => 'event_start',
                    'required'          => TRUE,                    
                    'showTime'          => TRUE,
                    'date_format_js'    => $date_format['js'],
                    'date_format_php'   => $date_format['php']
                ]);
                echo form_text('event_location', $this->locale['calendar_0264'], $this->calendar_data['event_location'], [
                    'required'   => FALSE,
                    'max_length' => 200,
                    'error_text' => $this->locale['calendar_0066']
                ]);
                closeside();

                openside($this->locale['calendar_0259']);
                $options = [];
                $calendar_result = dbquery("SELECT calendar_cat_id, calendar_cat_name FROM ".DB_CALENDAR_CATS." ORDER BY calendar_cat_name ASC");
                if (dbrows($calendar_result)) {
                    while ($calendar_data = dbarray($calendar_result)) {
                        $options[$calendar_data['calendar_cat_id']] = $calendar_data['calendar_cat_name'];
                    }
                }
                
                echo form_select('calendar_cat_id', $this->locale['calendar_0252'], $this->calendar_data['calendar_cat_id'], [
                    'inner_width' => '100%',
                    'inline'      => TRUE,
                    'options'     => $options,
                    'required'    => TRUE
                ]);

                echo form_select('event_visibility', $this->locale['calendar_0106'], $this->calendar_data['event_visibility'], [
                    'options'     => fusion_get_groups(),
                    'placeholder' => $this->locale['choose'],
                    'inner_width' => '100%',
                    'inline'      => TRUE,
                ]);
                if (multilang_table('CA')) {
                    echo form_select("event_language[]", $this->locale['language'], $this->calendar_data['event_language'], [
                        'options'     => fusion_get_enabled_languages(),
                        'placeholder' => $this->locale['choose'],
                        'inner_width' => '100%',
                        'inline'      => TRUE,
                        'multiple'    => TRUE
                    ]);
                } else {
                    echo form_hidden('event_language', '', $this->calendar_data['event_language']);
                }
                echo form_hidden('event_datestamp', '', $this->calendar_data['event_datestamp']);
                if (!empty($_GET['action']) && $_GET['action'] == 'edit') {
                    echo form_checkbox('update_datestamp', $this->locale['calendar_0257'], '');
                }
                closeside();
                openside($this->locale['calendar_0258']);
                echo form_checkbox('event_status', $this->locale['calendar_0255'], $this->calendar_data['event_status'], [
                    'class'         => 'm-b-5',
                    'reverse_label' => TRUE
                ]);
                if (self::$calendar_settings['calendar_allow_participation']) {
                    echo form_checkbox('event_participation', $this->locale['calendar_0275'], $this->calendar_data['event_participation'], [
                        'class'         => 'm-b-5',
                        'reverse_label' => TRUE
                    ]);
                } else {
                    echo form_hidden('event_participation', '', 0);
                }

                if (fusion_get_settings("tinymce_enabled") != 1) {
                    echo form_checkbox('event_breaks', $this->locale['calendar_0256'], $this->calendar_data['event_breaks'], [
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
        self::display_calendarButtons('formend', FALSE);
        echo closeform();
    }

    /**
     * Generate sets of push buttons for Content form
     *
     * @param      $unique_id
     * @param bool $breaker
     */
    private function display_calendarButtons($unique_id, $breaker = TRUE) {
        ?>
        <div class="m-t-20">
            <?php echo form_button("cancel", $this->locale['cancel'], $this->locale['cancel'], ["class" => "btn-default btn-sm", "icon" => "fa fa-times", "input_id" => "cancel-".$unique_id.""]); ?>
            <?php echo form_button("save", $this->locale['save'], $this->locale['save'], ["class" => "btn-success btn-sm m-l-5", "icon" => "fa fa-hdd-o", "input_id" => "save-".$unique_id.""]); ?>
            <?php echo form_button("save_and_close", $this->locale['save_and_close'], $this->locale['save_and_close'], ["class" => "btn-primary btn-sm m-l-5", "icon" => "fa fa-floppy-o", "input_id" => "save_and_close-".$unique_id.""]); ?>
        </div>
        <?php if ($breaker) { ?>
            <hr/><?php } ?>
        <?php
    }

    /**
     * Displays Listing
     */
    private function display_calendar_listing() {
        // Run functions
        $allowed_actions = array_flip(['publish', 'unpublish', 'delete', 'calendar_display']);

        // Table Actions
        if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {
            $input = (isset($_POST['event_id'])) ? explode(",", form_sanitizer($_POST['event_id'], "", "event_id")) : "";
            if (!empty($input)) {
                foreach ($input as $event_id) {
                    // check input table
                    if (dbcount("('event_id')", DB_CALENDAR_EVENTS, "event_id=:eventid", [':eventid' => intval($event_id)]) && \defender::safe()) {
                        switch ($_POST['table_action']) {
                            case 'publish':
                                dbquery("UPDATE ".DB_CALENDAR_EVENTS." SET event_status=:status WHERE event_id=:eventid", ['status' => '1', ':eventid' => intval($event_id)]);
                                addNotice("success", $this->locale['calendar_0037']);
                                break;
                            case 'unpublish':
                                dbquery("UPDATE ".DB_CALENDAR_EVENTS." SET event_status=:status WHERE event_id=:eventid", ['status' => '0', ':eventid' => intval($event_id)]);
                                addNotice("warning", $this->locale['calendar_0038']);
                                break;
                            case 'delete':
                                dbquery("DELETE FROM  ".DB_CALENDAR_EVENTS." WHERE event_id=:eventid", [':eventid' => intval($event_id)]);
                                addNotice('success', $this->locale['calendar_0032']);
                                break;
                            default:
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                redirect(FUSION_REQUEST);
            }
            addNotice('warning', $this->locale['calendar_0034']);
            redirect(FUSION_REQUEST);
        }

        if (isset($_POST['edit_calendar_cat']) && isset($_POST['calendar_cat_id']) && isnum($_POST['calendar_cat_id'])) {
            redirect(clean_request('cat_id='.$_POST['calendar_cat_id'].'&action=edit&ref=calendar_cat_form', ['action', 'cat_id', 'ref'], FALSE));
        }

        // delete category
        if (isset($_POST['delete_calendar_cat']) && isset($_POST['calendar_cat_id']) && isnum($_POST['calendar_cat_id'])) {
            // move everything to uncategorized.
            dbquery("UPDATE ".DB_CALENDAR_EVENTS." SET calendar_cat_id=:uncategorized WHERE calendar_cat_id=:calendar_cat_id", [':calendar_cat_id' => $_POST['calendar_cat_id'], ':uncategorized' => 0]);
            dbquery("DELETE FROM ".DB_CALENDAR_CATS." WHERE calendar_cat_id=:calendar_cat_id", [':calendar_cat_id' => $_POST['calendar_cat_id']]);
            addNotice('success', $this->locale['calendar_0041']);
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Clear
        if (isset($_POST['calendar_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Search
        $search_string = [];
        $sql_condition = multilang_table("CA") ? in_group('event_language', LANGUAGE) : "";
        if (isset($_POST['p-submit-event_description'])) {
            $search_string['event_description'] = [
                'input' => form_sanitizer($_POST['event_description'], '', 'event_description'), 'operator' => 'LIKE'
            ];
        }

        if (!empty($_POST['event_status']) && isnum($_POST['event_status']) && $_POST['event_status'] == '1') {
            $search_string['event_status'] = ['input' => 0, 'operator' => '='];
        }

        if (!empty($_POST['event_visibility'])) {
            $search_string['event_visibility'] = [
                'input' => form_sanitizer($_POST['event_visibility'], '', 'event_visibility'), 'operator' => '='
            ];
        }

        if (!empty($_POST['event_name'])) {
            $search_string['event_name'] = [
                'input' => form_sanitizer($_POST['event_name'], '', 'event_name'), 'operator' => '='
            ];
        }

        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                if ($sql_condition)
                    $sql_condition .= " AND ";
                $sql_condition .= "`$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
            }
        }

        $default_display = 16;
        $limit = $default_display;
        if ((!empty($_POST['calendar_display']) && isnum($_POST['calendar_display'])) || (!empty($_GET['calendar_display']) && isnum($_GET['calendar_display']))) {
            $limit = (!empty($_POST['calendar_display']) ? $_POST['calendar_display'] : $_GET['calendar_display']);
        }

        $rowstart = 0;
        $max_rows = dbcount("(event_id)", DB_CALENDAR_EVENTS, (multilang_table("CA") ? in_group('event_language', LANGUAGE) : ""));
        if (!isset($_POST['calendar_display'])) {
            $rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows ? $_GET['rowstart'] : 0);
        }

        $criteria = [
            'criteria' => "ac.*, IF(a.calendar_cat_name != '', a.calendar_cat_name, '".$this->locale['calendar_0010']."') 'calendar_cat_name', u.user_id, u.user_name, u.user_status, u.user_avatar",
            'join'     => "INNER JOIN ".DB_USERS." u ON u.user_id=ac.event_name
            LEFT JOIN ".DB_CALENDAR_CATS." a ON a.calendar_cat_id=ac.calendar_cat_id",
            'where'    => $sql_condition,
            //'sql_condition' => ,
            'limit'    => "LIMIT $rowstart, $limit"
        ];

        $result = self::CalendarData($criteria);
        // Query

        $info['limit'] = $limit;
        $info['rowstart'] = $rowstart;
        $info['max_rows'] = $max_rows;
        $info['calendar_rows'] = dbrows($result);
        // Filters
        $filter_values = [
            'event_title'   => !empty($_POST['event_title']) ? form_sanitizer($_POST['event_title'], '', 'event_title') : '',
            'event_description'     => !empty($_POST['event_description']) ? form_sanitizer($_POST['event_description'], '', 'event_description') : '',
            'event_status'     => !empty($_POST['event_status']) ? form_sanitizer($_POST['event_status'], 0, 'event_status') : '',
            'calendar_cat_id'     => !empty($_POST['calendar_cat_id']) ? form_sanitizer($_POST['calendar_cat_id'], 0, 'calendar_cat_id') : '',
            'event_visibility' => !empty($_POST['event_visibility']) ? form_sanitizer($_POST['event_visibility'], 0, 'event_visibility') : '',
            'event_name'       => !empty($_POST['event_name']) ? form_sanitizer($_POST['event_name'], '', 'event_name') : '',
        ];

        $filter_empty = TRUE;
        foreach ($filter_values as $val) {
            if ($val) {
                $filter_empty = FALSE;
            }
        }

        $calendar_cats = dbcount("(calendar_cat_id)", DB_CALENDAR_CATS);

        echo "<div class='m-t-15'>\n";
        echo openform('calendar_filter', 'post', FUSION_REQUEST);
        echo "<div class='clearfix'>\n";
        echo "<div class='pull-right'>\n";
        if ($calendar_cats) {
            echo "<a class='btn btn-success btn-sm' href='".clean_request('ref=calendar_form', ['ref'], FALSE)."'><i class='fa fa-plus'></i> ".$this->locale['calendar_0003']."</a>\n";
        }
        echo "<a class='m-l-5 btn btn-primary btn-sm' href='".clean_request('ref=calendar_cat_form', ['ref'], FALSE)."'><i class='fa fa-plus'></i> ".$this->locale['calendar_0119']."</a>
            <a class='m-l-5 btn btn-default btn-sm hidden-xs' onclick=\"run_admin('publish', '#table_action', '#calendar_table');\"><i class='fa fa-check'></i> ".$this->locale['publish']."</a>
            <a class='m-l-5 btn btn-default btn-sm hidden-xs' onclick=\"run_admin('unpublish', '#table_action', '#calendar_table');\"><i class='fa fa-ban'></i> ".$this->locale['unpublish']."</a>
            <a class='m-l-5 btn btn-danger btn-sm hidden-xs' onclick=\"run_admin('delete', '#table_action', '#calendar_table');\"><i class='fa fa-trash-o'></i> ".$this->locale['delete']."</a>
        </div><div class='display-inline-block pull-left m-r-10'>
        ".form_text('event_description', '', $filter_values['event_description'], [
                'placeholder'       => $this->locale['calendar_0120'],
                'append_button'     => TRUE,
                'append_value'      => '<i class=\'fa fa-search\'></i>',
                'append_form_value' => 'search_calendar',
                'width'             => '160px',
                'group_size'        => 'sm'
            ])."
        </div>
        <div class='display-inline-block va hidden-xs'>
            <a class='btn btn-sm m-r-15 ".($filter_empty ? 'btn-default' : 'btn-info')."' id='toggle_options' href='#'>".$this->locale['calendar_0120']."
                <span id='filter_caret' class='fa fa-fw ".($filter_empty ? 'fa-caret-down' : 'fa-caret-up')."'></span>
            </a>
            ".form_button('calendar_clear', $this->locale['calendar_0122'], 'clear', ['class' => 'btn-default btn-sm'])."
        </div>
        </div>
        <div id='calendar_filter_options' ".($filter_empty ? ' style=\'display: none;\'' : '').">
        <div class='display-inline-block'>
        ".form_select('event_status', '', $filter_values['event_status'], [
                'allowclear'  => TRUE,
                'placeholder' => '- '.$this->locale['calendar_0123'].' -',
                'options'     => [0 => $this->locale['calendar_0124'], 1 => $this->locale['draft']]
            ])."
        </div>
        <div class='display-inline-block'>
        ".form_select('event_visibility', '', $filter_values['event_visibility'], [
                'allowclear'  => TRUE,
                'placeholder' => '- '.$this->locale['calendar_0125'].' -',
                'options'     => fusion_get_groups()
            ])."
        </div><div class='display-inline-block'>\n";
        $author_opts = [0 => $this->locale['calendar_0131']];
        $result0 = dbquery('
                        SELECT n.event_name, u.user_id, u.user_name, u.user_status
                        FROM '.DB_CALENDAR_EVENTS.' n
                        LEFT JOIN '.DB_USERS.' u on n.event_name = u.user_id
                        GROUP BY u.user_id
                        ORDER BY user_name ASC
                    ');
        if (dbrows($result0) > 0) {
            while ($data = dbarray($result0)) {
                $author_opts[$data['user_id']] = $data['user_name'];
            }
        }
        echo form_select('event_name', '', $filter_values['event_name'], [
            'allowclear'  => TRUE,
            'placeholder' => '- '.$this->locale['calendar_0130'].' -',
            'options'     => $author_opts
        ]);
        echo "</div>\n</div>\n";
        echo closeform();
        echo "</div>\n";

        echo openform('calendar_table', 'post', FUSION_REQUEST);
        echo form_hidden('table_action', '', '');
        echo "<div class='display-block'>\n";

        // Category Management
        $cat_options = [];
        $cat_result = dbquery("SELECT * FROM ".DB_CALENDAR_CATS.(multilang_table('CA') ? " WHERE ".in_group('calendar_cat_language', LANGUAGE)." " : '')."ORDER BY calendar_cat_name ASC");
        if (dbrows($cat_result)) {
            echo "<div class='well'>\n";
            while ($cat_data = dbarray($cat_result)) {
                $cat_options[$cat_data['calendar_cat_id']] = $cat_data['calendar_cat_name'];
            }

            echo "<div class='row'>\n";
            echo "<div class='col-xs-12 col-sm-6'>\n";
            echo form_select('calendar_cat_id', $this->locale['calendar_0009'], '', ['inline' => TRUE, 'options' => $cat_options, 'class' => 'm-b-0']);
            echo "</div>\n<div class='col-xs-12 col-sm-6'>\n";
            echo form_button('edit_calendar_cat', $this->locale['edit'], $this->locale['edit'], ['class' => 'btn-default btn-sm']);
            echo form_button('delete_calendar_cat', $this->locale['delete'], $this->locale['delete'], ['class' => 'btn-danger btn-sm']);
            echo "</div>\n";
            echo "</div>\n";
            echo "</div>\n";
        }

        echo "<div class='table-responsive'><table class='table table-hover table-striped'>
            <thead>
            <tr>
            <th class='hidden-xs'></th>
            <th>".$this->locale['calendar_0100']."</td>
            <th>".$this->locale['calendar_0276']."</th>
            <th>".$this->locale['calendar_0252']."</th>
            <th>".$this->locale['calendar_0105']."</th>
            <th>".$this->locale['calendar_0102']."</th>
            <th>".$this->locale['calendar_0106']."</th>
            <th>".$this->locale['calendar_0107']."</th>
            </tr></thead>\n<tbody>\n";
        if (dbrows($result) > 0) {
            $_trash = ['section', 'ref', 'action', 'cat_id', 'event_id', 'calendar_cat_id'];
            while ($cdata = dbarray($result)) {

                $edit_link = clean_request("section=calendar&ref=calendar_form&action=edit&event_id=".$cdata['event_id'], $_trash, FALSE);
                $delete_link = clean_request("section=calendar&ref=calendar_form&action=delete&event_id=".$cdata['event_id'], $_trash, FALSE);
                $cat_edit_link = clean_request('section=calendar&ref=calendar_cat_form&action=edit&cat_id='.$cdata['calendar_cat_id'], $_trash, FALSE);
                echo "<tr data-id='".$cdata['calendar_cat_id']."'>
                        <td class='hidden-xs'>".form_checkbox("event_id[]", "", "", ['input_id' => 'calendar'.$cdata['event_id'], "value" => $cdata['event_id'], "class" => "m-0"])."</td>
                        <td><a href='$edit_link'>".$cdata['event_title']."</a></td>
                        <td>".Functions::cdatetime($cdata)."</td>
                        <td>".($cdata['calendar_cat_id'] ? "<a href='$cat_edit_link'>" : "").$cdata['calendar_cat_name'].($cdata['calendar_cat_id'] ? "</a>" : "")."</td>
                        <td>
                        <div class='pull-left'>".display_avatar($cdata, '20px', '', FALSE, 'img-rounded m-r-5')."</div>
                        <div class='overflow-hide'>".profile_link($cdata['user_id'], $cdata['user_name'], $cdata['user_status'])."</div>
                        </td>
                        <td>".($cdata['event_status'] ? $this->locale['no'] : $this->locale['yes'])."</td>
                        <td><span class='badge'>".getgroupname($cdata['event_visibility'])."</span></td>
                        <td>
                        <a href='$edit_link' title='".$this->locale['edit']."'>".$this->locale['edit']."</a>&nbsp;&middot;&nbsp;
                        <a href='$delete_link' title='".$this->locale['delete']."' onclick=\"return confirm('".$this->locale['calendar_0111']."')\">".$this->locale['delete']."</a>
                        </td></tr>\n";
            }
        } else {
            echo "<tr><td colspan='8' class='text-center'>".($calendar_cats ? $this->locale['calendar_0112'] : $this->locale['calendar_0114'])."</td></tr>";
        }
        echo "</tbody>\n</table>\n</div>";
        echo "<div class='display-inline-block'>\n
        ".form_select('calendar_display', $this->locale['calendar_0132'], $info['limit'], [
                'width'       => '70px',
                'inner_width' => '70px',
                'options'     => [5 => 5, 10 => 10, 16 => 16, 25 => 25, 50 => 50]])."
        </div>";
        if ($info['max_rows'] > $info['calendar_rows']) {
            echo "<div class='display-inline-block pull-right'>".makepagenav($info['rowstart'], $info['limit'], $info['max_rows'], 3, FUSION_SELF.fusion_get_aidlink().'&amp;calendar_display='.$info['limit'].'&amp;')."</div>";
        }
        echo "</div>\n";
        echo closeform();

        // jQuery
        add_to_jquery("
            // Toggle Filters
            $('#toggle_options').bind('click', function(e) {
                e.preventDefault();
                $('#calendar_filter_options').slideToggle();
                var caret_status = $('#filter_caret').hasClass('fa-caret-down');
                if (caret_status == 1) {
                    $('#filter_caret').removeClass('fa-caret-down').addClass('fa-caret-up');
                    $(this).removeClass('btn-default').addClass('btn-info');
                } else {
                    $('#filter_caret').removeClass('fa-caret-up').addClass('fa-caret-down');
                    $(this).removeClass('btn-info').addClass('btn-default');
                }
            });
            // Select Change
            $('#event_status, #event_visibility, #event_name, #calendar_display').bind('change', function(e) {
                $(this).closest('form').submit();
            });
        ");

    }

}
