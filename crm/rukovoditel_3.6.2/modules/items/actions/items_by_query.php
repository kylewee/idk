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

$field_query = db_query("select name, configuration, type, entities_id from app_fields where id=" . _GET('fields_id') . " and type='fieldtype_items_by_query'");
if(!$field = db_fetch_array($field_query))
{
    redirect_to('dashboard/page_not_found');
}

$item_query = db_query("select e.* " . fieldtype_formula::prepare_query_select($current_entity_id, '') . " from app_entity_" . $current_entity_id . " e where e.id='" . $current_item_id . "'");
if(!$item = db_fetch_array($item_query))
{
    redirect_to('dashboard/page_not_found');
}