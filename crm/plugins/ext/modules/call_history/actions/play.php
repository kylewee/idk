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

$call_history_query = db_query("select * from app_ext_call_history where id=" . _GET('id'));
if(!$call_history = db_fetch_array($call_history_query))
{
    redirect_to('ext/call_history/view');
}
elseif($call_history['is_new']==1)
{
    //set off new
    db_query("update app_ext_call_history set is_new=0 where id={$call_history['id']}");
}

switch ($app_module_action)
{
    case 'save':
        db_perform('app_ext_call_history', ['comments'=>$_POST['comments']], 'update', "id='" . db_input($_GET['id']) . "'");
        exit();
        break;				
}