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

require('includes/libs/SqlFormatter.php');

$log_info_query = db_query("select l.*,u.field_12 as username from app_logs l left join app_entity_1 u on u.id=l.users_id where log_type='mysql' and l.id=" . _GET('id'));
if(!$log_info = db_fetch_array($log_info_query))
{
    redirect_to('logs/view','type=mysql');
}
