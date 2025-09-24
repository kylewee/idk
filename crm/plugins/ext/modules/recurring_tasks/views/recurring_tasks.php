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
?>

<h3 class="page-title"><?php echo TEXT_EXT_RECURRING_TASKS ?></h3>

<p><?php echo TEXT_EXT_RECURRING_TASKS_INFO  . '<br>' . TEXT_EXT_RECURRING_TASKS_INFO_CRON . '<br>' . DIR_FS_CATALOG . 'cron/recurring_tasks.php'?></p>

<?php 

$tasks_query = db_query("select * from app_ext_recurring_tasks order by id");
$redirect_to = '&redirect_to=recurring_tasks';

require(component_path('ext/recurring_tasks/listing'));