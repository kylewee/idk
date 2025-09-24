ALTER TABLE `app_ext_kanban` ADD `filters_panel` VARCHAR(32) NOT NULL DEFAULT 'default' AFTER `width`, ADD `rows_per_page` SMALLINT NOT NULL DEFAULT '20' AFTER `filters_panel`;
ALTER TABLE `app_ext_kanban` ADD `assigned_to` VARCHAR(255) NOT NULL DEFAULT '' AFTER `users_groups`, ADD `is_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `assigned_to`;
ALTER TABLE `app_ext_ganttchart` ADD `is_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `entities_id`;
ALTER TABLE `app_ext_ganttchart` ADD `filters_panel` VARCHAR(32) NOT NULL DEFAULT 'default' AFTER `end_date`;
ALTER TABLE `app_ext_sms_rules` ADD `notes` TEXT NOT NULL DEFAULT '' AFTER `description`;
