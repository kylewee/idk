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


$ipage_query = db_query("select * from app_ext_ipages where id='" .  _GET('id') . "' and (find_in_set(" . $app_user['group_id'] . ",users_groups) or find_in_set(" . $app_user['id'] . ",assigned_to))");
if(!$ipage = db_fetch_array($ipage_query))
{
    die(TEXT_FILE_NOT_FOUD);
}

$file = attachments::parse_filename(base64_decode($_GET['file']));
    		  	
if(!is_file($file['file_path']))
{
    die(TEXT_FILE_NOT_FOUD);
}

$download_url = url_for('ext/ipages/view', 'id=' . _GET('id') . '&action=download_attachment&file=' . urlencode(base64_encode($file['file'])));

require(component_path('items/attachment_preview_text'));
