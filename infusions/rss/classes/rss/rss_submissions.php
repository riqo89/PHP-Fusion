<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss/classes/rss/rss_submissions.php
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
namespace PHPFusion\RSS;

class RssSubmissions extends RssServer {
    private static $instance = NULL;
    public $info = [];
    private $locale = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayRss() {
        $this->locale = fusion_get_locale("", RSS_LOCALE);
        add_to_title($this->locale['rss_0900']);
        $this->info['rss_tablename'] = $this->locale['rss_0900'];
        if (iMEMBER && self::$rss_settings['rss_allow_submission']) {
            display_rss_submissions($this->display_submission_form());
        } else {
            $info['no_submissions'] = $this->locale['rss_0922'];
            $info += $this->info;
            display_rss_submissions($info);
        }
    }

    private function display_submission_form() {
        $criteriaArray = [
            'rss_id'       => 0,
            'rss_cat_id'   => 0,
            'rss_language' => LANGUAGE,
            'rss_status'   => 1
        ];

        if (dbcount("(rss_cat_id)", DB_RSS_CATS, (multilang_table("RS") ? in_group('rss_cat_language', LANGUAGE) : ""))) {
            // Save
            if (check_post("submit_link")) {
                $criteriaArray = [
                    'rss_cat_id'    => form_sanitizer($_POST['rss_cat_id'], 0, 'rss_cat_id'),
                    'rss_title'     => form_sanitizer($_POST['rss_title'], '', 'rss_title'),
                    'rss_link'      => form_sanitizer($_POST['rss_link'], '', 'rss_link'),
                    'rss_language'  => form_sanitizer($_POST['rss_language'], LANGUAGE, 'rss_language'),
                    'rss_status'    => 1
                ];
                // Save
                if (fusion_safe()) {
                    $inputArray = [
                        'submit_type'      => 'r',
                        'submit_user'      => fusion_get_userdata('user_id'),
                        'submit_datestamp' => TIME,
                        'submit_criteria'  => \Defender::encode($criteriaArray)
                    ];
                    dbquery_insert(DB_SUBMISSIONS, $inputArray, 'save');
                    addNotice('success', $this->locale['rss_0910']);
                    redirect(clean_request('submitted=r', ['stype'], TRUE));
                }
            }

            if (get("submitted") === "r") {
                $info['confirm'] = [
                    'title'       => $this->locale['rss_0911'],
                    'submit_link' => "<a href='".BASEDIR."submit.php?stype=r'>".$this->locale['rss_0912']."</a>",
                    'index_link'  => "<a href='".BASEDIR."index.php'>".str_replace("[SITENAME]", fusion_get_settings("sitename"), $this->locale['rss_0913'])."</a>"
                ];
                $info += $this->info;
                return (array)$info;
            } else {
                $options = [];
                $rss_result = dbquery("SELECT rss_cat_id, rss_cat_name FROM ".DB_RSS_CATS.(multilang_table("RS") ? " WHERE ".in_group('rss_cat_language', LANGUAGE) : "")." ORDER BY rss_cat_name ASC");
                if (dbrows($rss_result)) {
                    $options[0] = $this->locale['rss_0010'];
                    while ($rss_data = dbarray($rss_result)) {
                        $options[$rss_data['rss_cat_id']] = $rss_data['rss_cat_name'];
                    }
                }

                $info['item'] = [
                    'guidelines'     => str_replace("[SITENAME]", fusion_get_settings("sitename"), $this->locale['rss_0920']),
                    'openform'       => openform('submit_form', 'post', BASEDIR."submit.php?stype=r", ['enctype' => self::$rss_settings['rss_allow_submission'] ? TRUE : FALSE]),
                    'rss_title'   => form_text('rss_title', $this->locale['rss_0100'], $criteriaArray['rss_title'],
                        [
                            'error_text' => $this->locale['rss_0271'],
                            'required'   => TRUE
                        ]),
                    'rss_link'   => form_text('rss_link', $this->locale['rss_0256'], $criteriaArray['rss_link'],
                        [
                            'error_text' => $this->locale['rss_0271'],
                            'required'   => TRUE
                        ]),
                    'rss_cat_id'     => form_select('rss_cat_id', $this->locale['rss_0252'], $criteriaArray['rss_cat_id'],
                        [
                            'inner_width' => '250px',
                            'inline'      => TRUE,
                            'options'     => $options
                        ]),
                    'rss_language'   => (multilang_table('RS') ? form_select('rss_language[]', $this->locale['language'], $criteriaArray['rss_language'],
                        [
                            'options'     => fusion_get_enabled_languages(),
                            'placeholder' => $this->locale['choose'],
                            'width'       => '250px',
                            'inline'      => TRUE,
                            'multiple'    => TRUE
                        ]) : form_hidden('rss_language', '', $criteriaArray['rss_language'])),
                    'rss_submit'     => form_button('submit_link', $this->locale['submit'], $this->locale['submit'], ['class' => 'btn-success', 'icon' => 'fa fa-fw fa-hdd-o']),
                    'criteria_array' => $criteriaArray

                ];
                $info += $this->info;
                return (array)$info;
            }
        } else {
            $info['no_submissions'] = $this->locale['rss_0923'];
            $info += $this->info;
            return (array)$info;
        }
    }
}
