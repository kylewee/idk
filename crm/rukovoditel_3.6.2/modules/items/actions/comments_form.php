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

//checking access
if(isset($_GET['id']) and !users::has_comments_access('update'))
{          
  echo ajax_modal_template(TEXT_WARNING, TEXT_NO_ACCESS);
  exit();
}
elseif(!users::has_comments_access('create'))
{
  echo ajax_modal_template(TEXT_WARNING, TEXT_NO_ACCESS);
  exit();
}

$entity_cfg = new entities_cfg($current_entity_id);