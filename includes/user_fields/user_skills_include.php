<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: user_skills_include.php
| Author: riqo89 (riqo89@gmail.com)
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

switch($profile_method) {
    case 'input':
        $option_list = $field_value ? explode(',', $field_value) : [];
        $options += ['options' => $option_list, 'tags' => TRUE, 'multiple' => TRUE, 'width' => '100%', 'inner_width' => '100%', 'tip' => $locale['uf_skills_desc']];    
        $user_fields = form_select('user_skills', $locale['uf_skills'], $field_value, $options);
    break;

    case 'display':      
        $tags = $field_value ? explode(',', $field_value) : []; 

        $field_value = array_map(function ($n) {
            return sprintf("<span class='badge strong m-5' style='font-size:1em;'>%s</span>", $n);
        }, $tags);

        $user_fields = [
            'value' => $field_value ? implode($field_value) : ''
        ];
    break;
}