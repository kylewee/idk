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

$form_entity = db_find('app_entities',str_replace('form_add_in','',$app_redirect_to));

$breadcrumb = array();

$breadcrumb[] = '<li>' . $form_entity['name'] . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . link_to(TEXT_NAV_FORM_CONFIG,url_for('entities/forms&entities_id=' . $form_entity['id'])) . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . TEXT_FORM_ADD_IN . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . $entity_info['name'] . '<i class="fa fa-angle-right"></i></li>';

$breadcrumb[] = '<li>' . TEXT_FILTERS . '</li>';

$page_description = TEXT_FORM_ADD_IN_FILTERS_TIP;
