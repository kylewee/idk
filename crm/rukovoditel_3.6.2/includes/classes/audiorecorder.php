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

class audiorecorder
{
    static function upload($field_id, $verifyToken)
    {
        global $uploadify_attachments, $uploadify_attachments_queue;
        
        //print_rr($_FILES);
        
        if(isset($_FILES['audio_data']['tmp_name']) and is_array($_FILES['audio_data']['tmp_name']))
        {
            foreach($_FILES['audio_data']['tmp_name'] as $key=>$tmp_name)
            {
                $count_audio = 0;
                foreach($uploadify_attachments[$field_id] as $v)
                {
                    if(strstr($v,TEXT_AUDIO_RECORD)) $count_audio++;
                }
                
                foreach($uploadify_attachments_queue[$field_id] as $v)
                {
                    if(strstr($v,TEXT_AUDIO_RECORD)) $count_audio++;
                }
                
                $time = $_FILES['audio_data']['name'][$key];
                $file = attachments::prepare_filename(TEXT_AUDIO_RECORD  . ' ' . ($count_audio+$key+1) . ' (' . $time . ').ogg'); 
                
                //print_rr($file);

                if (move_uploaded_file($tmp_name, DIR_WS_ATTACHMENTS . $file['folder'] . '/' . $file['file']))
                {
                    
                    //add attachments to tmp table
                    $sql_data = array('form_token' => $verifyToken, 'filename' => $file['name'], 'date_added' => date('Y-m-d'), 'container' => $field_id);
                    db_perform('app_attachments', $sql_data);
                }
            }
        }
    }
    
    static function upload_chat($verifyToken)
    {
        $count_audio=0;
                
        $attachments_query = db_query("select * from app_attachments where form_token='" . db_input($verifyToken) . "' and container=0");
        while($attachments = db_fetch_array($attachments_query))
        {
            if(strstr($attachments['filename'],TEXT_AUDIO_RECORD)) $count_audio++;
        }
                
        if(isset($_FILES['audio_data']['tmp_name']) and is_array($_FILES['audio_data']['tmp_name']))
        {
            foreach($_FILES['audio_data']['tmp_name'] as $key=>$tmp_name)
            {                                
                $time = $_FILES['audio_data']['name'][$key];
                $file = attachments::prepare_filename(TEXT_AUDIO_RECORD  . ' ' . ($count_audio+$key+1) . ' (' . $time . ').ogg'); 
                
                //print_rr($file);

                if (move_uploaded_file($tmp_name, DIR_WS_ATTACHMENTS . $file['folder'] . '/' . $file['file']))
                {
                    
                    //add attachments to tmp table
                    $sql_data = array('form_token' => $verifyToken, 'filename' => $file['name'], 'date_added' => date('Y-m-d'), 'container' => 0);
                    db_perform('app_attachments', $sql_data);
                }
            }
        }
    }
    
    static function upload_mail($verifyToken)
    {
        $count_audio=0;
                
        $attachments_query = db_query("select * from app_attachments where form_token='" . db_input($verifyToken) . "' and container=0");
        while($attachments = db_fetch_array($attachments_query))
        {
            if(strstr($attachments['filename'],TEXT_AUDIO_RECORD)) $count_audio++;
        }
                
        if(isset($_FILES['audio_data']['tmp_name']) and is_array($_FILES['audio_data']['tmp_name']))
        {
            foreach($_FILES['audio_data']['tmp_name'] as $key=>$tmp_name)
            {                                
                $time = $_FILES['audio_data']['name'][$key];
                $file = mail_info::prepare_attachment_filename(TEXT_AUDIO_RECORD  . ' ' . ($count_audio+$key+1) . ' (' . $time . ').ogg'); 
                
                //print_rr($file);

                if (move_uploaded_file($tmp_name, DIR_WS_MAIL_ATTACHMENTS . $file['folder'] . '/' . $file['file']))
                {
                    
                    //add attachments to tmp table
                    $sql_data = array('form_token' => $verifyToken, 'filename' => $file['name'], 'date_added' => date('Y-m-d'), 'container' => 0);
                    db_perform('app_attachments', $sql_data);
                }
            }
        }
    }
}
