<?php

define('TEXT_UPDATE_VERSION_FROM','3.5');
define('TEXT_UPDATE_VERSION_TO','3.6');

include('includes/template_top.php');

$columns_array = array();
$columns_query = db_query("SHOW COLUMNS FROM app_ext_sms_rules");
while($columns = db_fetch_array($columns_query))
{
  $columns_array[] = $columns['Field'];
}

//print_r($columns_array);

//check if we have to run update for current database
if(!in_array('notes',$columns_array))
{
    echo '<h3 class="page-title">' . TEXT_PROCESSING . '</h3>';
    
    //required sql update
    $sql = "
ALTER TABLE `app_ext_kanban` ADD `filters_panel` VARCHAR(32) NOT NULL DEFAULT 'default' AFTER `width`, ADD `rows_per_page` SMALLINT NOT NULL DEFAULT '20' AFTER `filters_panel`;
ALTER TABLE `app_ext_kanban` ADD `assigned_to` VARCHAR(255) NOT NULL DEFAULT '' AFTER `users_groups`, ADD `is_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `assigned_to`;
ALTER TABLE `app_ext_ganttchart` ADD `is_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `entities_id`;
ALTER TABLE `app_ext_ganttchart` ADD `filters_panel` VARCHAR(32) NOT NULL DEFAULT 'default' AFTER `end_date`;
ALTER TABLE `app_ext_sms_rules` ADD `notes` TEXT NOT NULL DEFAULT '' AFTER `description`;

";
    
    db_query_from_content(trim($sql));
    
    //if there are no any errors display success message
    echo '<div class="alert alert-success">' . TEXT_UPDATE_COMPLATED . '</div>';
}
else
{
    echo '<div class="alert alert-warning">' . TEXT_UPDATE_ALREADY_RUN . '</div>';
}

include('includes/template_bottom.php');