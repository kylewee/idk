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

switch ($app_module_action)
{    
    case 'upload':
        (new onlyoffice($current_entity_id))->upload();
        exit();
        break;
    case 'preview':
        echo (new onlyoffice($current_entity_id))->preview(_GET('field_id'),$_GET['form_token']??'',$current_item_id);
        exit();
        break;
    case 'download':
        onlyoffice::download($current_entity_id, $current_item_id,_GET('file'));
        break;
    case 'download_all':
        onlyoffice::download_all($current_entity_id, $current_item_id,_GET('field_id'));
        break;
    case 'delete':
        onlyoffice::delete(_POST('file'));
        exit();
        break;
}
