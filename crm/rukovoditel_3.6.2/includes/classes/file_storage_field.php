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

class file_storage_field
{
    static function get_types()
    {
        return [
            'fieldtype_dropbox',
            'fieldtype_yandex_disk',
            'fieldtype_google_drive',
        ];
    }
    static function remove_special_characters($string)
    {
        return preg_replace('/-+/', '-', preg_replace('/[^\w._-]+/u', '', preg_replace('/\s+/', '-', trim($string))));
    }
    
    static function upload($entity_id, $field_id)
    {        
        global $app_user, $app_session_token;
                
        if(isset($_SESSION['app_logged_users_id']) and $app_user['id']>0)
        {
            $verifyToken = md5($app_user['id'] . $_POST['timestamp']);
        }
        else
        {
            $verifyToken = md5($app_session_token . $_POST['timestamp']);
        }
                        
        //print_rr($_FILES);
                                        
        if (strlen($_FILES['Filedata']['tmp_name']) and $_POST['token'] == $verifyToken)
        {
            $filename = $_FILES['Filedata']['name'];
                                   
            $filename =  self::remove_special_characters($filename);
            
            $date_added = time();
                                                                        
            $sql_data = [
                'entity_id' => $entity_id,
                'field_id' => $field_id,
                'form_token' => $verifyToken,
                'filename' => $filename,                
                'date_added' => $date_added,                
                'filekey' => '',
                'created_by' => $app_user['id']                
            ];
            
            db_perform('app_file_storage', $sql_data);
            $file_id = db_insert_id();
                                    
            if(!is_dir(DIR_FS_FILE_STORAGE . $file_id))
            {
                mkdir(DIR_FS_FILE_STORAGE . $file_id);                                
            }

            if (move_uploaded_file($_FILES['Filedata']['tmp_name'], DIR_FS_FILE_STORAGE . $file_id . '/' . $filename))
            {        
                $folder = "ENTITY_{$entity_id}/FIELD_{$field_id}/YEAR_" . date('Y',$date_added) . '/MONTH_' . date('m',$date_added) . '/' . $file_id;
                
                $sql_data = [
                    'folder' => $folder,
                    'size' =>filesize(DIR_FS_FILE_STORAGE . $file_id . '/' . $filename)
                ];
                 
                 db_perform('app_file_storage', $sql_data,'update','id=' . $file_id);
                
                return $file_id;
            }
        }
        
        return false;
    }
    
    static function preview($entity_id, $field_id, $token, $item_id)
    {
        global $app_session_token, $app_module_path;

        $html = '';

        $field_query = db_query("select id, name, configuration,type from app_fields where id='" . $field_id . "'");
        if($field = db_fetch_array($field_query))
        {
            $cfg = new fields_types_cfg($field['configuration']);
        }
        else
        {
            return '';
        }
        
        //get attachments 
        $attachments = [];
        
        //get exist attachment for item
        if($item_id>0)
        {
            $item = db_find('app_entity_' . $entity_id,$item_id);
            if(isset($item['field_' . $field_id]) and strlen($item['field_' . $field_id]))
            {
                $files_query = db_query("select * from app_file_storage where id in (" . db_input_in($item['field_' . $field_id]) . ") and field_id='" . db_input($field_id) . "'", false);
                while ($file = db_fetch_array($files_query))
                {
                    $attachments[] = $file;
                }
            }
        }
        
        //get new attachments by form token
        if(strlen($token))
        {
            $files_query = db_query("select * from app_file_storage where form_token='" . db_input($token) . "' and field_id='" . db_input($field_id) . "'", false);
            while ($file = db_fetch_array($files_query))
            {
                $attachments[] = $file;
            }
        }
        
        if(!count($attachments)) return '';
                        
        //print_rr($attachments);
        
        //check delete access
        $has_delete_access = true;        
        if($cfg->get('check_delete_access')==1)
        {
            $has_delete_access = users::has_access('delete');
        }
                      
        foreach($attachments as $file)
        {           
            $filepathinfo = pathinfo($file['filename']);
                        
            $row_id = 'attachments_row_' . $field_id . '_' . $file['id'];

            $html .= '
                <div class="input-group input-group-attachments ' . $row_id . '">
                    ' . input_hidden_tag('fields[' . $field_id . '][' . $file['id'] . '][id]', $file['id']);
            
            
            $html .= input_tag('fields[' . $field_id . '][' . $file['id'] . '][name]',$file['filename'],['class'=>'form-control input-sm','readonly'=>'readonly']) ;
            

            if($has_delete_access)
            {
                $html .= '
                    <span class="input-group-btn">
                        <a href="#" class="btn btn-sm btn-default delete_attachments_link" data-id="' . $file['id'] . '" data-row_id="' . $row_id . '" data-name="' . $file['filename'] . '" title="' . TEXT_DELETE . '">
                            <i class="fa fa-trash-o pointer"></i>
                        </a>        
                    </span> 
                    
                    ';
            }

            $html .= ' 
                </div>
                ';
        }
        
        if($has_delete_access)
        {
            $html .= '
                    <script>
                        $("#uploadifive_attachments_list_' . $field_id. ' .delete_attachments_link").click(function(){
                            rowData = $(this).data()
                            info = $("#uploadifive_attachments_list_' . $field_id. '").data()
                            if(confirm(rowData.name+"\n' . addslashes(TEXT_DELETE_FILE). '?"))
                            {                                
                                $("."+rowData.row_id).fadeOut()
                                $.ajax({
                                    method: "POST",
                                    url: info.delete_url,
                                    data: {file: rowData.id}
                                }).done(function(){
                                    $("."+rowData.row_id).remove()
                                })                                
                            }
                            
                            return false;                            
                        })
                    </script>
                ';
        }
        
        return $html;
    }
    
    static function get_file_info($file)
    {
        $pathinfo = pathinfo($file['filename']);
        $extension = $pathinfo['extension']??'';
        
        if(is_file('images/fileicons/' . $extension . '.png'))
        {
            $icon = 'images/fileicons/' . $extension . '.png';
        }
        else
        {
            $icon = 'images/fileicons/attachment.png';
        }
        
        $size = attachments::file_size_convert($file['size']);            
        
        return [
                'size'=>$size,
                'icon'=>$icon,
                'name'=>$pathinfo['filename'],
                'extension'=>$extension,
            ];
    }
    
    static function delete_by_item_id($entity_id, $item_id)
    {
        global $app_fields_cache;
        
        $item = db_find('app_entity_' . $entity_id,$item_id);
                        
        foreach($app_fields_cache[$entity_id] as $field)
        {            
            if(in_array($field['type'],self::get_types()) and strlen($value = $item['field_' . $field['id']]))
            {                
                (new $field['type'])->delete($entity_id, $field['id'], $value);                                
            }
        }                
    }
}
