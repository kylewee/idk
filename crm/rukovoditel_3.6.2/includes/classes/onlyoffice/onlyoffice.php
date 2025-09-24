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

class onlyoffice
{
    private $entity_id;
    
    function __construct($entity_id)
    {
        $this->entity_id = $entity_id;
    }
    
    static function genFileKey($file_id)
    {                
        return $_SERVER['HTTP_HOST'] . '-' . $file_id . '-' . time();
    }
    
    static function callback_error_log($msg)
    {
        error_log(date('Y-m-d H:i:s') . ' Error: ' . $msg . "\n _GET params: " . print_r($_GET,true) . "\n\n",3,'log/onlyoffice_callback.log');
        
        $response = [
            'error'=>1,
            'status' => 'success'
            ];
        
       die(json_encode($response));
    }
    
    static function getDownloadUrl($file)
    {
        $download_token = $file['download_token'];
        if(!strlen($download_token))
        {
            $download_token = users::get_random_password(12, false);
            
            db_query("update app_onlyoffice_files set download_token='{$download_token}' where id={$file['id']}");
        }
        
        return url_for('onlyoffice/download','file=' . $file['id'] . '&field=' . $file['field_id']. '&token=' . $download_token);
    }
    
    /*
    * Defines the document type to be opened:
    * https://api.onlyoffice.com/editors/config/#documentType
    */ 
    static function getDocumentInfo($filename)
    {
        $documentType = [
            'word' =>['.djvu','.doc','.docm','.docx','.docxf','.dot','.dotm','.dotx','.epub','.fb2','.fodt','.htm','.html','.mht','.mhtml','.odt','.oform','.ott','.oxps','.pdf','.rtf','.stw','.sxw','.txt','.wps','.wpt','.xml','.xps'],
            'cell' =>['.csv','.et','.ett','.fods','.ods','.ots','.sxc','.xls','.xlsb','.xlsm','.xlsx','.xlt','.xltm','.xltx','.xml'],
            'slide' =>['.dps','.dpt','.fodp','.odp','.otp','.pot','.potm','.potx','.pps','.ppsm','.ppsx','.ppt','.pptm','.pptx','.sxi'],
        ];
        
        $pathinfo = pathinfo($filename);
        $extension = $pathinfo['extension']??'word';
        
        $documentInfo = [
            'documentType' => '',
            'fileType' => $extension,
        ];
        
        foreach($documentType as $k=>$v)
        {
            if(in_array('.' . $extension,$v))
            {
                $documentInfo['documentType'] = $k;
            }
        }
        
        return $documentInfo;
    }
    
    static function getMode($cfg,$entity_id, $item_id)
    {
        global $app_fields_cache, $app_user;
        
        $mode = 'view';
        
        switch($cfg->get('allow_edit'))
        {
            case 'users_view_access':
                $mode = 'edit';
                break;
            case 'users_edit_access':
                $item_info = db_find('app_entity_' . $entity_id,$item_id);
                $access_rules = new access_rules($entity_id, $item_info);
                if(users::has_access('update', $access_rules -> get_access_schema()))
                {
                    $mode = 'edit';
                }
                break;
            case 'assigned_users':
                $item_info = db_find('app_entity_' . $entity_id,$item_id);
                $assigned_users_fields = $cfg->get('assigned_users_fields');
                
                if(is_array($assigned_users_fields))
                {
                    foreach($assigned_users_fields as $field_id)
                    {
                        if($app_fields_cache[$entity_id][$field_id]['type']=='fieldtype_created_by')
                        {
                            if($item_info['created_by']==$app_user['id'])
                            {
                                $mode = 'edit';
                            }
                        }
                        elseif(isset($item_info['field_' . $field_id]) and in_array($app_user['id'],explode(',',$item_info['field_' . $field_id])))
                        {
                            $mode = 'edit';
                        }
                            
                    }
                }
                break;            
        }
        
        return $mode;
    }
    
    function upload()
    {        
        global $app_user,$app_session_token;
                
        if(isset($_SESSION['app_logged_users_id']))
        {
            $verifyToken = md5($app_user['id'] . $_POST['timestamp']);
        }
        else
        {
            $verifyToken = md5($app_session_token . $_POST['timestamp']);
        }
        
        $field_id = _GET('field_id');
                                        
        if (strlen($_FILES['Filedata']['tmp_name']) and $_POST['token'] == $verifyToken)
        {
            $filename = $_FILES['Filedata']['name'];
            
            if(isset($_POST['filename_template']) and strlen($_POST['filename_template']) and isset($_POST['form_data']))
            {
                $filename = attachments::get_filename_by_template($_POST['filename_template'],$_POST['form_data'],$filename);
            }
            else
            {            
                $filename =  $this->remove_special_characters($filename);
            }
            
            $sql_data = [
                'entity_id' => $this->entity_id,
                'field_id' => $field_id,
                'form_token' => $verifyToken,
                'filename' => $filename,                
                'date_added' => time(),
                'created_by' => $app_user['id']                
            ];
            
            db_perform('app_onlyoffice_files', $sql_data);
            $file_id = db_insert_id();
            
            $folder = $this->prepare_file_folder($filename);
            
            if(!is_dir(DIR_WS_ONLYOFFICE . $folder . '/' . $file_id))
            {
                mkdir(DIR_WS_ONLYOFFICE . $folder . '/' . $file_id);
                
                $folder  = $folder . '/' . $file_id;
            }

            if (move_uploaded_file($_FILES['Filedata']['tmp_name'], DIR_WS_ONLYOFFICE . $folder . '/' . $filename))
            {                
                 $sql_data = [
                     'folder' => $folder,
                     'filekey' => self::genFileKey($file_id),
                 ];
                 
                 db_perform('app_onlyoffice_files', $sql_data,'update','id=' . $file_id);
            }
        }
    }
    
    function copy_from_attachments($field_id, $attachments = [], $delete_source_file = true)
    {
        global $app_user;
        
        $onlyoffice_files = [];
        foreach($attachments as $attachment)
        {
            $file = attachments::parse_filename($attachment);
            
            if(!is_file($file['file_path'])) continue;
            
            $filename = $file['name'];
            
            $sql_data = [
                'entity_id' => $this->entity_id,
                'field_id' => $field_id,
                'form_token' => '',
                'filename' => $filename,                
                'date_added' => time(),
                'created_by' => $app_user['id']                
            ];
            
            db_perform('app_onlyoffice_files', $sql_data);
            $file_id = db_insert_id();
            
            $onlyoffice_files[] = $file_id;
            
            $folder = $this->prepare_file_folder($filename);
            
            if(!is_dir(DIR_WS_ONLYOFFICE . $folder . '/' . $file_id))
            {
                mkdir(DIR_WS_ONLYOFFICE . $folder . '/' . $file_id);
                
                $folder  = $folder . '/' . $file_id;
            }

            if (copy($file['file_path'], DIR_WS_ONLYOFFICE . $folder . '/' . $filename))
            {        
                if($delete_source_file)
                {
                    unlink($file['file_path']);
                }
                
                 $sql_data = [
                     'folder' => $folder,
                     'filekey' => self::genFileKey($file_id),
                 ];
                 
                 db_perform('app_onlyoffice_files', $sql_data,'update','id=' . $file_id);                 
            }                        
        }
        
        return $onlyoffice_files;
        
    }
    
    function remove_special_characters($string)
    {
        return preg_replace('/-+/', '-', preg_replace('/[^\w._-]+/u', '', preg_replace('/\s+/', '-', trim($string))));
    }
    
    function prepare_file_folder($filename)
    {
                
        if(!is_dir(DIR_WS_ONLYOFFICE . $this->entity_id))
        {
            mkdir(DIR_WS_ONLYOFFICE . $this->entity_id);
        }

        if(!is_dir(DIR_WS_ONLYOFFICE . $this->entity_id . '/' . date('Y')))
        {
            mkdir(DIR_WS_ONLYOFFICE . $this->entity_id . '/'  . date('Y'));
        }

        if(!is_dir(DIR_WS_ONLYOFFICE . $this->entity_id . '/'  . date('Y') . '/' . date('m')))
        {
            mkdir(DIR_WS_ONLYOFFICE . $this->entity_id . '/' . date('Y') . '/' . date('m'));
        }

        if(!is_dir(DIR_WS_ONLYOFFICE . $this->entity_id . '/' . date('Y') . '/' . date('m') . '/' . date('d')))
        {
            mkdir(DIR_WS_ONLYOFFICE . $this->entity_id . '/' . date('Y') . '/' . date('m') . '/' . date('d'));
        }

        return  $this->entity_id . '/' . date('Y') . '/' . date('m') . '/' . date('d');
    }
    
    function preview($field_id, $token, $item_id)
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
            $item = db_find('app_entity_' . $this->entity_id,$item_id);
            if(isset($item['field_' . $field_id]) and strlen($item['field_' . $field_id]))
            {
                $files_query = db_query("select * from app_onlyoffice_files where id in (" . db_input_in($item['field_' . $field_id]) . ") and field_id='" . db_input($field_id) . "'", false);
                while ($file = db_fetch_array($files_query))
                {
                    $attachments[] = $file;
                }
            }
        }
        
        //get new attachments by form token
        if(strlen($token))
        {
            $files_query = db_query("select * from app_onlyoffice_files where form_token='" . db_input($token) . "' and field_id='" . db_input($field_id) . "'", false);
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
        
        
        //check delete access
        $allow_change_file_name = false;
        if($cfg->get('allow_change_file_name')==1)
        {
            $allow_change_file_name = true;
        }
        
        
        foreach($attachments as $file)
        {           
            $filepathinfo = pathinfo($file['filename']);
                        
            $row_id = 'attachments_row_' . $field_id . '_' . $file['id'];

            $html .= '
                <div class="input-group input-group-attachments ' . $row_id . '">
                    ' . input_hidden_tag('fields[' . $field_id . '][' . $file['id'] . '][id]', $file['id']);
            
            if($allow_change_file_name)
            {
                $html .= input_tag('fields[' . $field_id . '][' . $file['id'] . '][name]',$filepathinfo['filename'],['class'=>'form-control input-sm']) ;

                if(strlen($filepathinfo['extension']??''))
                {
                    $html .= '
                        <span class="input-group-addon">
                            .' . $filepathinfo['extension'] . '
                        </span>
                        ';
                }
            }
            else
            {
                $html .= input_tag('fields[' . $field_id . '][' . $file['id'] . '][name]',$file['filename'],['class'=>'form-control input-sm','readonly'=>'readonly']) ;
            }

            if($has_delete_access)
            {
                $html .= '
                    <span class="input-group-addon">
                        <i class="fa fa-trash-o pointer delete_attachments_icon" data-id="' . $file['id'] . '" data-row_id="' . $row_id . '" data-name="' . $file['filename'] . '" title="' . TEXT_DELETE . '"></i>
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
                        $("#uploadifive_attachments_list_' . $field_id. ' .delete_attachments_icon").click(function(){
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
        
        if(is_file($file_path = DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename']))
        {            
            $size = attachments::file_size_convert(filesize($file_path));            
        }
        else
        {
            $size = 0;
        }
        
        return [
                'size'=>$size,
                'icon'=>$icon,
                'name'=>$pathinfo['filename'],
                'extension'=>$extension,
            ];
    }
    
    static function download($entity_id, $item_id, $file_id)
    {
        //check if ID exist in DB
        $file_query = db_query("select * from app_onlyoffice_files where id={$file_id}");
        if(!$file = db_fetch_array($file_query))
        {
            die(TEXT_FILE_NOT_FOUD);
        }
        
        //check if file exist on disk
        if(!is_file($file_path = DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename']))
        {
            die(TEXT_FILE_NOT_FOUD);
        }
        
        //check if file assigend to record
        $item = db_find('app_entity_' . $entity_id,$item_id);       
        if(isset($item['field_' . $file['field_id']]) and !in_array($file['field_id'],explode(',', $file['field_id'])))
        {
            die(TEXT_FILE_NOT_FOUD);
        }
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$file['filename']);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));

        flush();

        readfile($file_path);
        
        exit();
    }
    
    static function download_all($entity_id, $item_id, $field_id)
    {
        $item_query = db_query("select * from app_entity_{$entity_id} where id={$item_id}");
        if(!$item = db_fetch_array($item_query))
        {
            die(TEXT_FILE_NOT_FOUD);
        }
        
        if(!isset($item['field_' . $field_id]) or (isset($item['field_' . $field_id]) and !strlen($item['field_' . $field_id])))
        {
            die(TEXT_FILE_NOT_FOUD);
        }
        
        $zip = new ZipArchive();
        $zip_filename = "attachments-{$item_id}.zip";
        $zip_filepath = DIR_FS_UPLOADS . $zip_filename;                
        
        //open zip archive        
        $zip->open($zip_filepath, ZipArchive::CREATE);
                        
        //add files to archive   
        $files_query = db_query("select * from app_onlyoffice_files where find_in_set(id,'" . $item['field_' . $field_id] . "') and field_id='" . db_input($field_id) . "'", false);
        while ($file = db_fetch_array($files_query))
        {
           $zip->addFile(DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename'],$file['filename']);                                       
        }
                        
        $zip->close();
        
        //check if zip archive created
        if (!is_file($zip_filepath)) 
        {
            exit("Error: cannot create zip archive in " . $zip_filepath );
        }
        
        //download file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$zip_filename);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zip_filepath));
        
        flush();
              
        readfile($zip_filepath);   
        
        //delete temp zip archive file
        @unlink($zip_filepath); 
        
        exit();
    }
    
    static function delete($file_id)
    {
        $file_query = db_query("select * from app_onlyoffice_files where id={$file_id}");
        if($file = db_fetch_array($file_query))
        {
            if(is_file($file_path = DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename']))
            {
                unlink($file_path);
            }
            
            db_delete_row('app_onlyoffice_files', $file_id);
        }
    }
    
    public static function delete_attachments($entities_id, $items_id)
    {
        $items_query = db_query("select * from app_entity_" . $entities_id . " where id='" . db_input($items_id) . "'");
        if($items = db_fetch_array($items_query))
        {
            $fields_query = db_query("select * from app_fields where entities_id='" . db_input($entities_id) . "' and type in ('fieldtype_onlyoffice')");
            while($fields = db_fetch_array($fields_query))
            {            
                if(strlen($files = $items['field_' . $fields['id']]) > 0)
                {
                    $files_query = db_query("select * from app_onlyoffice_files where field_id='" . db_input($fields['id']) . "' and id in (" . db_input_in($files) . ")", false);
                    while ($file = db_fetch_array($files_query))
                    {
                        if(is_file($filepath = DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename']))
                        {
                            unlink($filepath);
                        }
                        
                        db_delete_row('app_onlyoffice_files', $file['id']);
                    }
                }
            }
        }
    }
}
