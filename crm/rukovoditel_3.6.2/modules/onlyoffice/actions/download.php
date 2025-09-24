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

$field_id = _GET('field');
$file_id = _GET('file');
$token = _GETS('token');
$file_query = db_query("select * from app_onlyoffice_files where field_id={$field_id} and id={$file_id} and download_token='{$token}'");
if(!$file = db_fetch_array($file_query))
{
    die(TEXT_FILE_NOT_FOUD);
}

if(!is_file($filepath = DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename']))
{
    die(TEXT_FILE_NOT_FOUD);
}

header("Content-type: " . mime_content_type($filepath));
header('Content-Disposition: filename="' . $file['filename'] . '"');

flush();

readfile($filepath);

exit();
