<?php

/* 
 *  Этот файл является частью программы "CRM Руководитель" - конструктор CRM систем для бизнеса
 *  https://www.rukovoditel.net.ru/
 *  
 *  CRM Руководитель - это свободное программное обеспечение, 
 *  распространяемое на условиях GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 *  
 *  Автор и правообладатель программы: Харчишина Ольга Александровна (RU), Харчишин Сергей Васильевич (RU).
 *  Государственная регистрация программы для ЭВМ: 2023664624
 *  https://fips.ru/EGD/3b18c104-1db7-4f2d-83fb-2d38e1474ca3
 */


$assigned_to = (int)$_GET['assigned_to'];

if($_GET['is_conversation']==1)
{
    $chat_conversation_query = db_query("select * from app_ext_chat_conversations where id='" . $assigned_to . "' and (users_id='" . $app_user['id']. "' or find_in_set('" . $app_user['id'] . "',assigned_to))");
    if(!$chat_conversation = db_fetch_array($chat_conversation_query))
    {
        die(TEXT_EXT_CHAT_CONVERSATION_IS_NOT_FOUD);
    }
}
else
{    
    $chat_user_query = db_query("select u.*,a.name as group_name,u.field_6 as group_id from app_entity_1 u left join app_access_groups a on a.id=u.field_6 where u.id='" . db_input($assigned_to) . "'");
    if(!$chat_user = db_fetch_array($chat_user_query))
    {
            echo '<div class="alert alert-warning">' . TEXT_USER_IS_NOT_FOUD. '</div>';	
            exit();
    }

    //check access
    if(!$app_chat->has_access_by_group($chat_user['group_id']))
    {
            echo '<div class="alert alert-warning">' . TEXT_USER_IS_NOT_FOUD. '</div>';	
            exit();
    }
}

switch($app_module_action)
{
    case 'upload':
        
        $verifyToken = $_GET['attachments_form_token'];
        
        audiorecorder::upload_chat($verifyToken);
        
        exit();
        break;
}

