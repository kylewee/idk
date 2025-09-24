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

chdir(substr(__DIR__,0,-4));

define('IS_CRON',true);

//load core
require('includes/application_core.php');


//load app lang
if(is_file($v = 'includes/languages/' . CFG_APP_LANGUAGE))
{
	require($v);
}

//load ext lang
if(is_file($v = 'plugins/ext/languages/' . CFG_APP_LANGUAGE))
{
	require($v);
}
	
$app_user = array('id'=>0,'group_id'=>0,'language'=>CFG_APP_LANGUAGE);

$modules = new modules('payment');

if(isset($_REQUEST['module_id']))
{	
	$modules->ipn((int)$_REQUEST['module_id']);
}
else 
{
	die('Error: module_id is not available!');
}	