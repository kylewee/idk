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

$template_info_query = db_query("select ep.*, e.name as entities_name from app_ext_comments_templates ep, app_entities e where e.id=ep.entities_id and ep.id='" . db_input($_GET['templates_id']) . "' order by e.id, ep.sort_order, ep.name");
if(!$template_info = db_fetch_array($template_info_query))
{  
  redirect_to('ext/templates/comments_templates');
}

$obj = array();

if(isset($_GET['id']))
{
  $obj = db_find('app_ext_comments_templates_fields',$_GET['id']);      
}
else
{
  $obj = db_show_columns('app_ext_comments_templates_fields');
}
