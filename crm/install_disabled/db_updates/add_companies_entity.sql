-- Add Companies entity to the CRM
-- This migration adds a new Companies entity with basic fields

-- Add the Companies entity
INSERT INTO `app_entities` (`id`, `parent_id`, `group_id`, `name`, `notes`, `display_in_menu`, `sort_order`) VALUES
(25, 0, 0, 'Companies', 'Company management', 1, 2);

-- Add entity configuration
INSERT INTO `app_entities_configuration` (`entities_id`, `configuration_name`, `configuration_value`) VALUES
(25, 'menu_title', 'Companies'),
(25, 'listing_heading', 'Companies'),
(25, 'window_heading', 'Company Info'),
(25, 'insert_button', 'Add Company'),
(25, 'use_comments', '1');

-- Add entity access for different user groups
-- Access group 6 (view assigned)
INSERT INTO `app_entities_access` (`entities_id`, `access_groups_id`, `access_schema`) VALUES
(25, 6, 'view_assigned');

-- Access group 5 (view, create, update, reports)
INSERT INTO `app_entities_access` (`entities_id`, `access_groups_id`, `access_schema`) VALUES
(25, 5, 'view,create,update,reports');

-- Access group 4 (full access)
INSERT INTO `app_entities_access` (`entities_id`, `access_groups_id`, `access_schema`) VALUES
(25, 4, 'view,create,update,delete,reports');

-- Create form tab for Companies
INSERT INTO `app_forms_tabs` (`entities_id`, `parent_id`, `is_folder`, `name`, `description`, `sort_order`) VALUES
(25, 0, 0, 'Info', '', 0);

-- Get the last inserted tab ID for use in fields
SET @tab_id = LAST_INSERT_ID();

-- Add fields for Companies entity
-- Action field
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_action', '', 0, 1, 0);

-- ID field
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_id', '', 0, 1, 1);

-- Date Added field
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_date_added', '', 0, 1, 6);

-- Created By field
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_created_by', '', 0, 1, 7);

-- Company Name field (required, heading, searchable)
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `is_heading`, `is_required`, `configuration`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_input', 'Company Name', 1, 1, '{"allow_search":"1","width":"input-xlarge"}', 2, 1, 3);

-- Industry field (dropdown)
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `configuration`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_dropdown', 'Industry', '{"width":"input-large"}', 3, 1, 4);

-- Get the last inserted field ID for Industry dropdown
SET @industry_field_id = LAST_INSERT_ID();

-- Add choices for Industry field
INSERT INTO `app_fields_choices` (`fields_id`, `name`, `sort_order`) VALUES
(@industry_field_id, 'Automotive', 1),
(@industry_field_id, 'Technology', 2),
(@industry_field_id, 'Healthcare', 3),
(@industry_field_id, 'Manufacturing', 4),
(@industry_field_id, 'Retail', 5),
(@industry_field_id, 'Service', 6),
(@industry_field_id, 'Other', 7);

-- Company Website field
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `configuration`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_input_url', 'Website', '{"width":"input-xlarge"}', 4, 0, 0);

-- Phone Number field
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `configuration`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_input', 'Phone', '{"width":"input-medium"}', 5, 1, 5);

-- Email field
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `configuration`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_input_email', 'Email', '{"allow_search":"1","width":"input-large"}', 6, 1, 8);

-- Address field (textarea)
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `configuration`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_textarea', 'Address', '{"height":"60"}', 7, 0, 0);

-- Notes field (textarea)
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `configuration`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_textarea', 'Notes', '{"height":"80"}', 8, 0, 0);

-- Status field (dropdown)
INSERT INTO `app_fields` (`entities_id`, `forms_tabs_id`, `type`, `name`, `is_required`, `configuration`, `sort_order`, `listing_status`, `listing_sort_order`) VALUES
(25, @tab_id, 'fieldtype_dropdown', 'Status', 1, '{"width":"input-medium"}', 1, 1, 2);

-- Get the last inserted field ID for Status dropdown
SET @status_field_id = LAST_INSERT_ID();

-- Add choices for Status field
INSERT INTO `app_fields_choices` (`fields_id`, `name`, `sort_order`, `is_default`) VALUES
(@status_field_id, 'Active', 1, 1),
(@status_field_id, 'Inactive', 2, 0),
(@status_field_id, 'Prospect', 3, 0);

-- Create data table for Companies entity
CREATE TABLE IF NOT EXISTS `app_entity_25` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_item_id` int(11) NOT NULL DEFAULT 0,
  `date_added` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_parent_item_id` (`parent_item_id`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
