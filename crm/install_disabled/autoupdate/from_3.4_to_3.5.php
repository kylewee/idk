<?php

define('TEXT_UPDATE_VERSION_FROM','3.4');
define('TEXT_UPDATE_VERSION_TO','3.5');

include('includes/template_top.php');

$tables_array = array();
$tables_query = db_query("show tables");
while($tables = db_fetch_array($tables_query))
{
    $tables_array[] = current($tables);
}

//print_r($columns_array);

//check if we have to run updat for current database
if(!in_array('app_file_storage',$tables_array))
{
    echo '<h3 class="page-title">' . TEXT_PROCESSING . '</h3>';
    
    //required sql update
    $sql = "
ALTER TABLE `app_access_groups` CHANGE `name` `name` VARCHAR(255);

CREATE TABLE IF NOT EXISTS `app_onlyoffice_files` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_id` int(10) UNSIGNED NOT NULL,
  `field_id` int(10) UNSIGNED NOT NULL,
  `form_token` varchar(64) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `filekey` varchar(255) NOT NULL,
  `download_token` varchar(32) NOT NULL COMMENT 'For public download',
  `date_added` bigint(20) UNSIGNED NOT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entity_id` (`entity_id`),
  KEY `idx_field_id` (`field_id`),
  KEY `idx_form_token` (`form_token`),
  KEY `idx_filename` (`filename`),
  KEY `idx_download_token` (`download_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `app_approved_items` CHANGE `signature` `signature` LONGTEXT NOT NULL;

ALTER TABLE `app_forms_tabs` ADD `icon` VARCHAR(64) NOT NULL AFTER `name`, ADD `icon_color` VARCHAR(7) NOT NULL AFTER `icon`;

CREATE TABLE IF NOT EXISTS `app_file_storage` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_id` int(10) UNSIGNED NOT NULL,
  `field_id` int(10) UNSIGNED NOT NULL,
  `form_token` varchar(64) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `size` int(10) UNSIGNED NOT NULL,
  `sort_order` int(11) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `filekey` varchar(255) NOT NULL,
  `date_added` bigint(20) UNSIGNED NOT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entity_id` (`entity_id`),
  KEY `idx_field_id` (`field_id`),
  KEY `idx_form_token` (`form_token`),
  KEY `idx_filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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