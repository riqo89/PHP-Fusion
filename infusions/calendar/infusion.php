<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion.php
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
defined('IN_FUSION') || exit;

$locale = fusion_get_locale("", CALENDAR_LOCALE);

// Infusion general information
$inf_title = $locale['calendar_setup_01'];
$inf_description = $locale['calendar_setup_02'];
$inf_version = "1.0";
$inf_developer = "riqo";
$inf_email = "dev@corico.cloud";
$inf_weburl = "https://www.phpfusion.com";
$inf_folder = "calendar";
$inf_image = "calendar.svg";

// Create tables
$inf_newtable[] = DB_CALENDAR_EVENTS." (
    event_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    calendar_cat_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    event_title VARCHAR(200) NOT NULL DEFAULT '',
    event_description TEXT NOT NULL,
    event_allday TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    event_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
    event_end INT(10) UNSIGNED NOT NULL DEFAULT '0',
    event_location VARCHAR(200) NOT NULL DEFAULT '',
    event_attachment_url TEXT NOT NULL,
    event_attachment_file VARCHAR(100) NOT NULL DEFAULT '',
    event_breaks CHAR(1) NOT NULL DEFAULT '',
    event_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
    event_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    event_visibility CHAR(4) NOT NULL DEFAULT '0',
    event_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    event_participation TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    event_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY(event_id),
    KEY calendar_cat_id (calendar_cat_id),
    KEY event_datestamp (event_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_CALENDAR_CATS." (
    calendar_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    calendar_cat_name VARCHAR(200) NOT NULL DEFAULT '',
    calendar_cat_description VARCHAR(250) NOT NULL DEFAULT '',
    calendar_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY(calendar_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_CALENDAR_PARTICIPATION." (
    participant_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    event_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    participant_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
    participant_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    participant_note TEXT NOT NULL,
    participant_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY(participant_id),
    KEY event_id (event_id),
    KEY participant_datestamp (participant_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_pagination', '15', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_pagination_archive', '50', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_allow_submission', '1', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_allow_participation', '1', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_allow_attachments', '1', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_attachment_max_b', '15728640', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_attachment_types', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_format_shorttime', '%R', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_format_longtime', '%T', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_format_shortdate', '%d.%m.%Y', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_format_longdate', '%d. %B %Y', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_format_titledate_day', '%A, %d. %B %Y', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_format_titledate_month', '%B %Y', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_format_titledate_year', '%Y', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('calendar_show_gmaps_iframe', '1', '".$inf_folder."')";

// Multilanguage table
$inf_mlt[] = [
    "title"  => $locale['calendar_setup_03'],
    "rights" => "CA"
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        if (file_exists(LOCALE.$language.'/setup.php')) {
            include LOCALE.$language.'/setup.php';
        } else {
            include LOCALE.'English/setup.php';
        }

        $mlt_adminpanel[$language][] = [
            "rights"   => "CA",
            "image"    => $inf_image,
            "title"    => $locale['calendar_setup_03'],
            "panel"    => "calendar_admin.php",
            "page"     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['calendar_setup_04']."', 'infusions/".$inf_folder."/calendar.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['calendar_setup_05']."', 'submit.php?stype=c', ".USER_LEVEL_MEMBER.", '1', '0', '23', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/calendar.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=c' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_CALENDAR_CATS." WHERE calendar_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_CALENDAR_EVENTS." WHERE event_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='CA' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        "rights"   => "CA",
        "image"    => $inf_image,
        "title"    => $locale['calendar_setup_03'],
        "panel"    => "calendar_admin.php",
        "page"     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['calendar_setup_04']."', 'infusions/".$inf_folder."/calendar.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['calendar_setup_05']."', 'submit.php?stype=c', ".USER_LEVEL_MEMBER.", '1', '0', '23', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_CALENDAR_CATS;
$inf_droptable[] = DB_CALENDAR_EVENTS;
$inf_droptable[] = DB_CALENDAR_PARTICIPATION;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='CA'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/calendar.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=c'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='CA'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='c'";
$inf_delfiles[] = CALENDAR_ATTACHMENTS;