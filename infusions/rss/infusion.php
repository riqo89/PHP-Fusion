<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion.php
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
defined('IN_FUSION') || exit;

$locale = fusion_get_locale("", RSS_LOCALE);

// Infusion general information
$inf_title = $locale['rss_setup_01'];
$inf_description = $locale['rss_setup_02'];
$inf_version = "1.0";
$inf_developer = "riqo";
$inf_email = "dev@corico.cloud";
$inf_weburl = "https://github.com/riqo89/PHP-Fusion";
$inf_folder = "rss";
$inf_image = "rss.svg";

// Create tables
$inf_newtable[] = DB_RSS." (
    rss_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    rss_cat_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    rss_title VARCHAR(200) NOT NULL DEFAULT '',
    rss_content TEXT NOT NULL,
    rss_link VARCHAR(255) NOT NULL DEFAULT '',
    rss_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
    rss_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    rss_visibility CHAR(4) NOT NULL DEFAULT '0',
    rss_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    rss_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY(rss_id),
    KEY rss_cat_id (rss_cat_id),
    KEY rss_datestamp (rss_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_RSS_CATS." (
    rss_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    rss_cat_name VARCHAR(200) NOT NULL DEFAULT '',
    rss_cat_description VARCHAR(250) NOT NULL DEFAULT '',
    rss_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY(rss_cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert settings
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('rss_allow_submission', '1', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('rss_cronjob_hour', '".TIME."', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('rss_cronjob_refresh', '1800', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('rss_cronjob_include', '1', '".$inf_folder."')";

// Multilanguage table
$inf_mlt[] = [
    "title"  => $locale['setup_3011'],
    "rights" => "RS"
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
            "rights"   => "RS",
            "image"    => $inf_image,
            "title"    => $locale['rss_setup_03'],
            "panel"    => "rss_admin.php",
            "page"     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['rss_setup_04']."', 'infusions/".$inf_folder."/rss.php', '0', '2', '0', '2', '1', '".$language."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['rss_setup_05']."', 'submit.php?stype=r', ".USER_LEVEL_MEMBER.", '1', '0', '23', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/rss.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=r' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_RSS_CATS." WHERE rss_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_RSS." WHERE rss_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='RS' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        "rights"   => "RS",
        "image"    => $inf_image,
        "title"    => $locale['rss_setup_03'],
        "panel"    => "rss_admin.php",
        "page"     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['rss_setup_04']."', 'infusions/".$inf_folder."/rss.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['rss_setup_05']."', 'submit.php?stype=r', ".USER_LEVEL_MEMBER.", '1', '0', '23', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_RSS_CATS;
$inf_droptable[] = DB_RSS;
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='RS'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/".$inf_folder."/rss.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='submit.php?stype=r'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='RS'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='r'";
