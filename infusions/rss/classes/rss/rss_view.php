<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: rss/classes/rss/rss_view.php
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

/**
 * Controller package
 * Class RssView
 *
 * @package PHPFusion\Rss
 */
class RssView extends Rss {

    /**
     * Displays RSS
     */
    public function display_rss() {
        if (isset($_GET['cat_id'])) {
            if (isnum($_GET['cat_id'])) {
                $info = $this->set_RssInfo($_GET['cat_id']);
                render_rss_item($info);
            } else {
                redirect(INFUSIONS."rss/rss.php");
            }
        } else {
            display_main_rss($this->set_RssInfo());
        }
    }
}
