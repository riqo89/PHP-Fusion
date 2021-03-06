<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss/templates.php
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

if (!function_exists('display_main_rss')) {
    function display_main_rss($info) {
        $html = \PHPFusion\Template::getInstance('main_rss');
        $html->set_template(__DIR__.'/templates/main_rss.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['rss_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('cat_locale', $info['cat_locale']);

        if (!empty($info['rss_categories'])) {
            foreach ($info['rss_categories'] as $cat_data) {
                $html->set_block('categories', [
                    'rss_cat_id'          => $cat_data['rss_cat_id'],
                    'rss_cat_link'        => $cat_data['rss_cat_link'],
                    'rss_cat_name'        => $cat_data['rss_cat_name'],
                    'rss_cat_description' => $cat_data['rss_cat_description']
                ]);
            }
        } else {
            $html->set_block('no_item', ['message' => fusion_get_locale('rss_0112a')]);
        }

        echo $html->get_output();
    }
}

if (!function_exists('render_rss_item')) {
    function render_rss_item($info) {
        $locale = fusion_get_locale("", RSS_LOCALE); $i = 0;
        $html = \PHPFusion\Template::getInstance('rss_item');
        $html->set_template(__DIR__.'/templates/rss_info.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['rss_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('cat_locale', $info['cat_locale']);
        $html->set_tag('cat_top', $info['cat_top']);
        $html->set_tag('rss_get_name', $info['rss_get_name']);
        $html->set_tag('rss_get_desc', $info['rss_get_desc']);

        if (!empty($info['rss_items'])) {
            add_to_jquery('$(".top").on("click",function(e){e.preventDefault();$("html, body").animate({scrollTop:0},100);});');
            foreach ($info['rss_items'] as $rss_data) {
                $html->set_block('rss', [
                    'rss_id'       => $rss_data['rss_id'],
                    'rss_collapse' => $i++ == 0 ? " in" : "",
                    'rss_title'    => $rss_data['rss_title'],
                    'rss_content'  => render_rss_content($rss_data['rss_content'], ['max_items' => 20]),
                    'rss_link'     => "<i class='fas fa-rss-square m-r-5'></i><a href='".$rss_data['rss_link']."' title='".$rss_data['rss_link']."'>".$locale['rss_0256']."</a>",
                    'rss_date'     => "<i class='fa fa-calendar m-r-5'></i><span title='".sprintf($locale['rss_0262'], showdate("longdate", $rss_data['rss_datestamp']))."'>".showdate("forumdate", $rss_data['rss_datestamp'])."</span>",
                    'edit_link'    => !empty($rss_data['edit']['link']) ? "<a href='".$rss_data['edit']['link']."' title='".$rss_data['edit']['title']."'><i class='fa fa-fw fa-pencil'></i></a>" : '',
                    'delete_link'  => !empty($rss_data['delete']['link']) ? "<a href='".$rss_data['delete']['link']."' title='".$rss_data['delete']['title']."'><i class='fa fa-fw fa-trash'></i></a>" : ''
                ]);
            }
        } else {
            $html->set_block('no_item', ['message' => fusion_get_locale('rss_0112')]);
        }

        echo $html->get_output();
    }
}

if (!function_exists('display_rss_submissions')) {
    function display_rss_submissions($info) {
        $html = \PHPFusion\Template::getInstance('rss_submissions');
        $html->set_template(__DIR__.'/templates/rss_submissions.html');
        $html->set_tag('opentable', fusion_get_function('opentable', $info['rss_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        if (!empty($info['item'])) {
            $html->set_block('rss_submit', [
                'guidelines'   => $info['item']['guidelines'],
                'openform'     => $info['item']['openform'],
                'closeform'    => closeform(),
                'rss_title'    => $info['item']['rss_title'],
                'rss_link'     => $info['item']['rss_link'],
                'rss_content'  => $info['item']['rss_content'],
                'rss_cat_id'   => $info['item']['rss_cat_id'],
                'rss_language' => $info['item']['rss_language'],
                'rss_submit'   => $info['item']['rss_submit']
            ]);
        }

        if (!empty($info['confirm'])) {
            $html->set_block('rss_confirm_submit', [
                'title'       => $info['confirm']['title'],
                'submit_link' => $info['confirm']['submit_link'],
                'index_link'  => $info['confirm']['index_link']
            ]);
        }

        if (!empty($info['no_submissions'])) {
            $html->set_block('rss_no_submit', ['text' => $info['no_submissions']]);
        }

        echo $html->get_output();
    }
}

if (!function_exists('render_rss_content')) {
    function render_rss_content($content, array $options = []) {
       
        $default_options = [
            'show_description'      => TRUE,
            'show_pubDate'          => TRUE,
            'show_enclosure'        => 'image',
            'show_link_host'        => FALSE,
            'max_items'             => 0,
            'max_desc_length'       => 250,
            'is_decoded'            => FALSE
        ];
        
        $options += $default_options;
        $locale = fusion_get_locale("", RSS_LOCALE);
                
        if(!$options['is_decoded']) $content = \Defender::decode($content);
        if($options['max_items']) $content = array_slice($content, 0, $options['max_items']);

        $output = '<div class="container-fluid p-0">';
        foreach($content as $key => $item) {
            $enclosure = ['video' => '', 'image' => ''];
            
            if($key != 0) $output .= '<hr class="m-t-0 m-b-10">';
            $output .= '<div class="row m-b-5">';                

                if($options['show_enclosure']) {
                    $output .= '<div class="col-md-2 m-t-5">';
                    if(preg_match("/video/", $item['enclosure']['type']) && !empty($item['enclosure']['url'])) {
                        $enclosure['video'] .= '<video style="object-fit: cover; width: 100%; height: 100px;" src="'.$item['enclosure']['url'].'" alt="'.$item['title'].'"></video>';
                    } 
                    if(preg_match("/image/", $item['enclosure']['type']) && !empty($item['enclosure']['url'])) {
                        $enclosure['image'] .= '<a href="'.$item['link'].'" title="'.$item['title'].'" alt="'.$item['title'].'">';
                        $enclosure['image'] .= '<img class="img-rounded" style="object-fit: cover; width: 100%; height: 100px;" src="'.$item['enclosure']['url'].'" alt="'.$item['title'].'" />';
                        $enclosure['image'] .= '</a>';
                    } else {
                        $enclosure['image'] .= '<a href="'.$item['link'].'" title="'.$item['title'].'" alt="'.$item['title'].'">';
                        $enclosure['image'] .= '<img class="img-rounded hidden-xs" style="object-fit: cover; width: 100%; height: 100px;" src="'.IMAGES.'no_photo.png" alt="'.$item['title'].'" />';
                        $enclosure['image'] .= '</a>';
                    }

                    switch($options['show_enclosure']) {  
                        case 'video':   $output .= $enclosure['video']; break;
                        case 'image':   $output .= $enclosure['image']; break;
                        case 'both':    $output .= $enclosure['image'].$enclosure['video']; break;                   
                    }
                    $output .= '</div>';
                }

                $output .= '<div class="col-md-'.($options['show_enclosure'] ? 10 : 12).' m-t-5">';
                    $output .= '<div class="row">'; 


                        $output .= '<div class="'.($options['show_pubDate'] ? 'col-md-8 col-xs-7' : 'col-md-12 col-xs-12').'" '.(!$options['show_pubDate'] ? 'title="'.showdate("longdate", $item['pubDate']).'"' : '').'>';
                        $output .= '<a href="'.$item['link'].'" target="_blank">'.$item['title'].'</a>';
                        $output .= '</div>';

                        if($options['show_pubDate'] && !empty($item['pubDate'])) {
                            $output .= '<div class="col-md-4 col-xs-5 text-right">';
                            $output .= '<i class="fa fa-calendar m-r-5"></i>'.timer($item['pubDate']);
                            $output .= '</div>';
                        }

                    $output .= '</div><div class="row">';
                        $output .= '<div class="col-md-12 m-t-5">'; 

                            if($options['show_description'] && !empty($item['description'])) {   
                                $output .= "<span title='".$item['description']."'>";         
                                $output .= trimlink(strip_tags(parse_textarea($item['description'], FALSE, TRUE)), $options['max_desc_length']);      
                                $output .= "</span>";          
                            }

                            if($options['show_link_host']) {
                                $output .= "<p class='pull-right small m-b-0 m-t-3' title='".$item['link']."'><em>";
                                $output .= parse_url($item['link'])['host'];
                                $output .= "</em></p>";
                            }

                        $output .= '</div>';
                    $output .= '</div>';
                $output .= '</div>';
            $output .= '</div>';
        }
        $output .= '</div>';
        
        return $output;
    }
}