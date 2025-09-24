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

$field_id = _GET('field_id');
if(!isset_field($current_entity_id,$field_id))
{
    redirect_to('items/info','path=' . $app_path);
}

$item_info_query = db_query("select field_{$field_id} from app_entity_{$current_entity_id} where id={$current_item_id}");
if(!$item_info = db_fetch_array($item_info_query))
{
    redirect_to('dashboard/page_not_found');
}

switch($app_module_action)
{
    case 'sort':
        $choices_sorted = $_POST['choices_sorted'];
        if(strlen($choices_sorted))
        {
            $choices_sorted = json_decode(stripslashes($choices_sorted), true);
            if($choices_sorted)
            {
                
                $attachments = [];
                foreach($choices_sorted as $file)
                {
                    $attachments[] = $file['id'];
                }
                
                db_query("update app_entity_{$current_entity_id} set field_{$field_id}='" . implode(',',$attachments). "' where id={$current_item_id}");
            }
        }
        redirect_to('items/info','path=' . $app_path);
        break;
}


