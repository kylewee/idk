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

$email_info_query = db_query("select * from app_ext_mail where groups_id='" . _get::int('id') . "' and accounts_id in (select accounts_id from app_ext_mail_accounts_users where users_id='" . $app_user['id'] . "') order by date_added limit 1");
if(!$email_info = db_fetch_array($email_info_query))
{
	redirect_to('dashboard/access_forbidden');
}


$file = mail_info::parse_attachment_filename(base64_decode($_GET['file']));

$download_url = url_for('ext/mail/info', 'id=' . $email_info['groups_id'] . '&action=download_attachment&file=' . urlencode(base64_encode($file['file'])));
    		  	
require(component_path('items/attachment_preview_text'));

