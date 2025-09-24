<?php

$message_info_query = db_query("select * from app_ext_chat_conversations_messages where id='" . _GET('id') . "' and users_id='" . $app_user['id'] . "'");
if(!$message_info = db_fetch_array($message_info_query))
{
    die(TEXT_NO_RECORDS_FOUND);
}

switch($app_module_action)
{
    case 'save':
        $sql_data = array(            
            'message' => db_prepare_html_input(str_replace('<div>&nbsp;</div>','',$_POST['chat_message'])),
        );

        db_perform('app_ext_chat_conversations_messages', $sql_data,'update','id=' . _GET('id'));
        exit();
        break;
    case 'refresh':
        echo $app_chat->render_message_template($message_info,_GET('assigned_to'),true);
        
        echo '<script>$(".fancybox-ajax").fancybox({type: "ajax",helpers: {overlay : {closeClick: false}}})</script>';
        
        exit();
        break;
}
