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

	$breadcrumb = array();
	
	$breadcrumb[] = '<li>' . link_to(TEXT_EXT_PROCESSES,url_for('ext/processes/processes')) . '<i class="fa fa-angle-right"></i></li>';
	
	$breadcrumb[] = '<li>' . link_to($app_process_info['name'],url_for('ext/processes/actions','process_id=' . $app_process_info['id'])) . '</li>';
	
	if(isset($app_actions_info))
	{
		$actions_types = processes::get_actions_types_choices($app_process_info['entities_id']);
		$breadcrumb[] = '<li><i class="fa fa-angle-right"></i>' . $actions_types[$app_actions_info['type']] . '</li>';
	}
?>

<ul class="page-breadcrumb breadcrumb">
  <?php echo implode('',$breadcrumb) ?>  
</ul>