<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss/admin/controllers/rss_submissions.inc
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

class RssSubmissionsAdmin extends RssAdminModel {
    private static $instance = NULL;
    private static $defArray = [
        'rss_visibility' => 0,
        'rss_status'     => 1,
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
    public function displayRssAdmin() {
        pageAccess("RS");

        $this->locale = self::get_rssAdminLocale();
        // Handle a Submission
        if (isset($_GET['submit_id']) && isNum($_GET['submit_id']) && dbcount("(submit_id)", DB_SUBMISSIONS, "submit_id=:submitid AND submit_type=:submittype", [':submitid' => $_GET['submit_id'], ':submittype' => 'r'])) {
            $criteria = [
                'criteria'  => ", u.user_id, u.user_name, u.user_status, u.user_avatar",
                'join'      => "LEFT JOIN ".DB_USERS." AS u ON u.user_id=s.submit_user",
                'where'     => 's.submit_type=:submit_type AND s.submit_id=:submit_id',
                'wheredata' => [
                    ':submit_id'   => $_GET['submit_id'],
                    ':submit_type' => 'r'
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
            dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submitid AND submit_type=:submittype", [':submitid' => $_GET['submit_id'], ':submittype' => 'r']);
            addNotice('success', $this->locale['rss_0062']);
            redirect(clean_request('', ['submit_id'], FALSE));
        }
    }

    private function handlePostSubmission() {
        if (isset($_POST['publish_submission']) || isset($_POST['preview_submission'])) {

            $SaveinputArray = [
                'rss_title'   => form_sanitizer($_POST['rss_title'], '', 'rss_title'),
                'rss_cat_id'     => form_sanitizer($_POST['rss_cat_id'], 0, 'rss_cat_id'),
                'rss_visibility' => form_sanitizer($_POST['rss_visibility'], 0, 'rss_visibility'),
                'rss_datestamp'  => form_sanitizer($_POST['rss_datestamp'], time(), 'rss_datestamp'),
                'rss_name'       => form_sanitizer($_POST['rss_name'], 0, 'rss_name'),
                'rss_link'       => form_sanitizer($_POST['rss_link'], '', 'rss_link'),
                'rss_status'     => isset($_POST['rss_status']) ? '1' : '0',
                'rss_language'   => form_sanitizer($_POST['rss_language'], LANGUAGE, 'rss_language')
            ];

            // Handle
            if (\defender::safe()) {

                // Publish Submission
                if (isset($_POST['publish_submission'])) {
                    dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submitid AND submit_type=:submittype", [':submitid' => $_GET['submit_id'], ':submittype' => 'r']);
                    $rss_dblast_id = dbquery_insert(DB_RSS, $SaveinputArray, 'save');
                    \PHPFusion\RSS\RssServer::Rss()->refresh_RssFeeds($rss_dblast_id);
                    addNotice('success', ($SaveinputArray['rss_status'] ? $this->locale['rss_0060'] : $this->locale['rss_0061']));
                    redirect(clean_request('', ['submit_id'], FALSE));
                }

                // Preview Submission
                if (isset($_POST['preview_submission'])) {
                    $footer = openmodal("rss_preview", "<i class='fa fa-eye fa-lg m-r-10'></i> ".$this->locale['preview'].": ".$SaveinputArray['rss_title']);
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

        // Start Form
        echo openform('submissionform', 'post', FUSION_REQUEST);
        echo form_hidden('rss_name', '', $this->inputArray['user_id']);
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
                $submissionInfo = strtr($this->locale['rss_0350']."<br />".$this->locale['rss_0351'], $replacements);

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
                echo form_text('rss_title', $this->locale['rss_0100'], $this->inputArray['rss_title'],
                    [
                        'required'   => TRUE,
                        'max_lenght' => 200,
                        'error_text' => $this->locale['rss_0270']
                    ]);

                echo form_text('rss_link', $this->locale['rss_0256'], $this->inputArray['rss_link'],
                    [
                        'required'   => TRUE,
                        'max_length' => 255
                    ]);
    
                ?>
            </div>

            <!-- Display Right Column -->
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                <?php

                openside($this->locale['rss_0259']);
                $options = [];
                $rss_result = dbquery("SELECT rss_cat_id, rss_cat_name FROM ".DB_RSS_CATS.(multilang_table("RS") ? " WHERE ".in_group('rss_cat_language', LANGUAGE) : "")." ORDER BY rss_cat_name ASC");
                if (dbrows($rss_result)) {
                    $options[0] = $this->locale['rss_0010'];
                    while ($rss_data = dbarray($rss_result)) {
                        $options[$rss_data['rss_cat_id']] = $rss_data['rss_cat_name'];
                    }
                }
                echo form_select('rss_cat_id', $this->locale['rss_0252'], $this->inputArray['rss_cat_id'],
                    [
                        'inner_width' => '100%',
                        'inline'      => TRUE,
                        'options'     => $options
                    ]);
                echo form_select('rss_visibility', $this->locale['rss_0106'], $this->inputArray['rss_visibility'],
                    [
                        'inline'      => TRUE,
                        'placeholder' => $this->locale['choose'],
                        'inner_width' => '100%',
                        'options'     => fusion_get_groups()
                    ]);

                if (multilang_table('RS')) {
                    echo form_select('rss_language[]', $this->locale['language'], $this->inputArray['rss_language'],
                        [
                            'inline'      => TRUE,
                            'placeholder' => $this->locale['choose'],
                            'inner_width' => '100%',
                            'options'     => fusion_get_enabled_languages(),
                            'multiple'    => TRUE
                        ]);
                } else {
                    echo form_hidden('rss_language', "", $this->inputArray['rss_language']);
                }

                echo form_datepicker('rss_datestamp', $this->locale['rss_0203'], $this->inputArray['submit_datestamp'],
                    [
                        'inline'      => TRUE,
                        'inner_width' => '100%'
                    ]);

                closeside();

                openside($this->locale['rss_0259']);

                echo form_checkbox('rss_status', $this->locale['rss_0255'], $this->inputArray['rss_status'],
                    [
                        'class'         => 'm-b-5',
                        'reverse_label' => TRUE
                    ]);

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
                ':submit_type' => 'r'
            ]

        ];
        $data = self::submitData($criteria);

        if (!empty($data)) {
            echo "<div class='well m-t-15'>".sprintf($this->locale['rss_0064'], format_word(count($data), $this->locale['fmt_submission']))."</div>\n";
            echo "<div class='table-responsive m-t-10'><table class='table table-striped'>\n";
            echo "<thead>\n";
            echo "<tr>\n";
            echo "<th class='strong'>".$this->locale['rss_0200']."</th>\n";
            echo "<th class='strong col-xs-5'>".$this->locale['rss_0100']."</th>\n";
            echo "<th class='strong'>".$this->locale['rss_0202']."</th>\n";
            echo "<th class='strong'>".$this->locale['rss_0203']."</th>\n";
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
                echo "<td><a href='".$reviewLink."'>".$submitData['rss_title']."</a></td>\n";
                echo "<td>".$submitUser."</td>\n";
                echo "<td>".timer($info['submit_datestamp'])."</td>\n";
                echo "</tr>\n";
            }

            echo "</tbody>\n";
            echo "</table>\n</div>";
        } else {
            echo "<div class='well text-center m-t-20'>".$this->locale['rss_0063']."</div>\n";
        }

    }
}
