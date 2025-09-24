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

if(!mail_accounts::user_has_access())
{
	redirect_to('dashboard/access_forbidden');
}

if(isset($_GET['mail_id']))
{	
	$email_info_query = db_query("select * from app_ext_mail where id='" . _get::int('mail_id') . "' and accounts_id in (select accounts_id from app_ext_mail_accounts_users where users_id='" . $app_user['id'] . "')");
	if(!$email_info = db_fetch_array($email_info_query))
	{
		redirect_to('dashboard/access_forbidden');
	}
}


$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_ext_mail_filters',$_GET['id']);
}
else
{
	$obj = db_show_columns('app_ext_mail_filters');
	
	$obj['accounts_id'] = ($app_mail_filters['accounts_id']>0 ? $app_mail_filters['accounts_id'] : mail_accounts::get_default());
}