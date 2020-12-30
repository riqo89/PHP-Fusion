<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: search_calendars_include_button.php
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
namespace PHPFusion\Search;

defined('IN_FUSION') || exit;

if (defined('CALENDAR_EXIST')) {
    $form_elements = &$form_elements;
    $radio_button = &$radio_button;
    $form_elements += [
        'calendar' => [
            'enabled'   => ['0' => 'datelimit', '1' => 'fields1', '2' => 'fields2', '3' => 'fields3', '4' => 'sort', '5' => 'order1', '6' => 'order2'],
            'disabled'  => ['0' => 'chars'],
            'display'   => [],
            'nodisplay' => [],
        ]
    ];
    $radio_button += [
        'calendar' => form_checkbox('stype', fusion_get_locale('ca400', CALENDAR."locale/".LOCALESET."search/calendar.php"), Search_Engine::get_param('stype'),
            [
                'type'          => 'radio',
                'value'         => 'calendar',
                'reverse_label' => TRUE,
                'onclick'       => 'display(this.value)',
                'input_id'      => 'calendar',
                'class'         => 'm-b-0'
            ]
        )
    ];
}
