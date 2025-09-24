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

/**
 *add info pages
 */

$menu = ipages::build_menu();

if(count($menu))   
{
    if(!isset($app_plugin_menu['menu'])) $app_plugin_menu['menu'] = [];
    
    $app_plugin_menu['menu'] = array_merge($app_plugin_menu['menu'],$menu);        
}


//
if((in_array($app_user['id'],explode(',',CFG_IPAGES_ACCESS_TO_USERS)) and strlen(CFG_IPAGES_ACCESS_TO_USERS)) or 
		(in_array($app_user['group_id'],explode(',',CFG_IPAGES_ACCESS_TO_USERS_GROUP)) and strlen(CFG_IPAGES_ACCESS_TO_USERS_GROUP)))
{
	$app_plugin_menu['menu'][] = array('title'=>TEXT_EXT_MENU_IPAGES,'url'=>url_for('ext/ipages/configuration'),'class'=>'fa-info-circle');	
}
