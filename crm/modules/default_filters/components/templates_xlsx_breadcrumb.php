<?php
/**
 * Этот файл является частью программы "CRM Руководитель" - конструктор CRM систем для бизнеса
 * https://www.rukovoditel.net.ru/
 * 
 * CRM Руководитель - это свободное программное обеспечение, 
 * распространяемое на условиях GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * 
 * Автор и правообладатель программы: Харчишина Ольга Александровна (RU), Харчишин Сергей Васильевич (RU).
 * Государственная регистрация программы для ЭВМ: 2023664624
 * https://fips.ru/EGD/3b18c104-1db7-4f2d-83fb-2d38e1474ca3
 */

$report_info_query = db_query("select t.id, t.name as template_name, f.entities_id  from app_ext_export_templates t, app_ext_items_export_templates_blocks tb left join app_fields f on tb.fields_id=f.id  where t.id = tb.templates_id and  tb.id='" . str_replace('templates_xlsx_block','',$app_redirect_to) . "'");
$block_report_info = db_fetch_array($report_info_query);

$breadcrumb = array();

$breadcrumb[] = '<li>' . link_to(TEXT_EXT_EXPORT_TEMPLATES, url_for('ext/templates/export_templates')) . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . $block_report_info['template_name'] . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . $entity_info['name']  . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . TEXT_FILTERS . '</li>';

