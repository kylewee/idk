ALTER TABLE `app_fields_choices` ADD `icon` VARCHAR(64) NOT NULL DEFAULT '' AFTER `name`;
ALTER TABLE `app_global_lists_choices` ADD `icon` VARCHAR(64) NOT NULL DEFAULT '' AFTER `name`;
ALTER TABLE `app_listing_highlight_rules` ADD `users_groups` TEXT NOT NULL DEFAULT '' AFTER `bg_color`;

CREATE TABLE IF NOT EXISTS `app_composite_unique_fields` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `entities_id` int(10) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `is_unique_for_parent` tinyint(1) NOT NULL,
  `field_1` int(10) UNSIGNED NOT NULL,
  `field_2` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `app_who_is_online` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `users_id` int(11) NOT NULL,
  `date_updated` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `app_last_user_action` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `users_id` int(10) UNSIGNED NOT NULL,
  `date` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `app_blocked_forms` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `date` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entity_id` (`entity_id`),
  KEY `idx_item_id` (`item_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `app_login_attempt` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_ip` varchar(64) NOT NULL,
  `count_attempt` smallint(5) UNSIGNED NOT NULL,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `date_banned` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_ip` (`user_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `app_comments` ADD INDEX `idx_entities_items` (`entities_id`, `items_id`);


