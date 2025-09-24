<?php

define('TEXT_UPDATE_VERSION_FROM','3.4');
define('TEXT_UPDATE_VERSION_TO','3.5');

include('includes/template_top.php');

$columns_array = array();
$columns_query = db_query("SHOW COLUMNS FROM app_ext_processes_actions_fields");
while($columns = db_fetch_array($columns_query))
{
  $columns_array[] = $columns['Field'];
}

//print_r($columns_array);

//check if we have to run update for current database
if(!in_array('allowed_value',$columns_array))
{
    echo '<h3 class="page-title">' . TEXT_PROCESSING . '</h3>';
    
    //required sql update
    $sql = "
ALTER TABLE `app_ext_resource_timeline` ADD `group_by_field` INT NOT NULL AFTER `fields_in_listing`;

ALTER TABLE `app_ext_items_export_templates_blocks` ADD `extra_type` VARCHAR(32) NOT NULL AFTER `block_type`;
ALTER TABLE `app_ext_items_export_templates_blocks` ADD `notes` TEXT NOT NULL AFTER `settings`;

ALTER TABLE `app_ext_call_history` ADD INDEX `idx_date_added` (`date_added`);

ALTER TABLE `app_ext_report_page` ADD `in_dashboard` TINYINT(1) NOT NULL AFTER `is_active`, ADD INDEX `idx_in_dashboard` (`in_dashboard`);

ALTER TABLE `app_ext_chat_messages` ADD `reply_id` INT UNSIGNED NOT NULL AFTER `id`, ADD INDEX `id_reply_id` (`reply_id`);
ALTER TABLE `app_ext_chat_conversations_messages` ADD `reply_id` INT UNSIGNED NOT NULL AFTER `id`, ADD INDEX `id_reply_id` (`reply_id`);

ALTER TABLE `app_ext_track_changes_log_fields` ADD `previous_value` TEXT NOT NULL AFTER `value`;

ALTER TABLE `app_ext_process_form_tabs` ADD `icon` VARCHAR(64) NOT NULL AFTER `name`, ADD `icon_color` VARCHAR(7) NOT NULL AFTER `icon`;

ALTER TABLE `app_ext_report_page` ADD `icon` VARCHAR(64) NOT NULL AFTER `type`, ADD `icon_color` VARCHAR(7) NOT NULL AFTER `icon`;

ALTER TABLE `app_ext_processes_actions_fields` ADD `allowed_value` TEXT NOT NULL AFTER `value`;
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