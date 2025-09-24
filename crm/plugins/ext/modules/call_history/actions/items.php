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

$entity_id = _GET('entity_id');

$call_history_query = db_query("select * from app_ext_call_history where id=" . _GET('id'));
if(!$call_history = db_fetch_array($call_history_query))
{
    redirect_to('ext/call_history/view');
}

if(!users::has_users_access_to_entity($entity_id))
{
    redirect_to('dashboard/access_forbidden');
}
