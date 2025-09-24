<?php

$message_info_query = db_query("select * from app_ext_chat_messages where id='" . _GET('id') . "'");
if(!$message_info = db_fetch_array($message_info_query))
{
    die(TEXT_NO_RECORDS_FOUND);
}

switch($app_module_action)
{
    case 'preview':
        echo $app_chat->render_message_reply_template($message_info);                        
        exit();
        break;
}
