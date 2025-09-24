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

$page_info = db_find('app_help_pages',str_replace('help_pages','',$app_redirect_to));

$breadcrumb = array();

$breadcrumb[] = '<li>' . link_to(TEXT_HELP_SYSTEM,url_for('help_pages/pages','entities_id=' . $page_info['entities_id'])) . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . $entity_info['name'] . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . (strlen($page_info['name']) ? $page_info['name'] : app_truncate_text($page_info['description'])) . '<i class="fa fa-angle-right"></i></li>';



$breadcrumb[] = '<li>' . TEXT_FILTERS . '</li>';

