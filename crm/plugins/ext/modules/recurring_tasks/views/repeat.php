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


$app_breadcrumb = items::get_breadcrumb($current_path_array);
$app_breadcrumb[] = array('title'=>TEXT_EXT_REPEAT);

require(component_path('items/navigation'));
 
echo button_tag(TEXT_EXT_CREATE_REPEAT,url_for('ext/recurring_tasks/form','path=' . $app_path)); 

$tasks_query = db_query("select * from app_ext_recurring_tasks where items_id = '" . $current_item_id . "' order by id");
$redirect_to = '';
require(component_path('ext/recurring_tasks/listing'));

echo '<a href="' . url_for('items/info','path=' . $app_path) . '" class="btn btn-default">' . TEXT_BUTTON_BACK. '</a>';