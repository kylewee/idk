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

class api_attachments extends api
{
    private $entity_id;
    private $field_id;
    private $item_id;
    private $user;
    private $item;
    private $attachment_value;
    
    private const SUPPORTED_FIELD_TYPES = [
        'fieldtype_attachments',
        'fieldtype_input_file',
        'fieldtype_image',
        'fieldtype_image_ajax',
        'fieldtype_onlyoffice'];
    
    function __construct($user)
    {
        global $app_entities_cache, $app_fields_cache;
        
        $this->user = $user;
        
        $this->entity_id = (int)self::_post('entity_id');        
        $this->field_id = (int)self::_post('field_id');
        $this->item_id = (int)self::_post('item_id');
        
        //check entity
        if(!isset_entity($this->entity_id))
        {
            api::response_error('Entity #' . $this->entity_id . ' does not exist');
        }  
                
        //check filed
        if(!isset_field($this->entity_id,$this->field_id))
        {
            api::response_error('Field #' . $this->field_id . ' does not exist in Entity #' . $this->entity_id);
        } 
                
        //check item
        $item_query = db_query("select field_{$this->field_id} from app_entity_{$this->entity_id} where id='" . db_input($this->item_id) . "'");
        if(!$this->item = db_fetch_array($item_query))
        {
            api::response_error('Item #' . $this->item_id . ' does not exist in Entity #' . $this->entity_id);
        }
                              
    }
    
    public function delete()
    {
        global $app_entities_cache, $app_fields_cache;
        
        //check field type        
        if(!in_array($field_type = $app_fields_cache[$this->entity_id][$this->field_id]['type'], self::SUPPORTED_FIELD_TYPES))
        {
            api::response_error('Field type #' . $field_type . ' not supported to download');
        }
        
        //check value
        if(!strlen($this->attachment_value = $this->item['field_' . $this->field_id]))
        {
            $data = [
                'filename' => '',                
            ];
            
            self::response_success($data);
        }
        
        //update db
        db_query("update app_entity_{$this->entity_id} set field_{$this->field_id}='' where id={$this->item_id}");
        
        if($field_type == 'fieldtype_onlyoffice')
        {
            $this->delete_onlyoffice();
        }
        else
        {
            $this->delete_attachment();
        }
                                
        
        
    }
    
    private function delete_attachment()
    {                
        foreach(explode(',',$this->attachment_value) as $file)
        {
            $file = attachments::parse_filename($file);
            if(is_file(DIR_WS_ATTACHMENTS . $file['folder'] . '/' . $file['file_sha1']))
            {
                unlink(DIR_WS_ATTACHMENTS . $file['folder'] . '/' . $file['file_sha1']);

                //delete image preview if exist
                attachments::delete_image_preview($file);
            }
        }
        
        $data = [
            'filename' => $this->attachment_value,                
            ];
            
        self::response_success($data);
    }
    
    private function delete_onlyoffice()
    {       
        $filename = [];
        $files_query = db_query("select * from app_onlyoffice_files where field_id='" . db_input($this->field_id) . "' and id in (" . db_input_in($this->attachment_value) . ")", false);
        while ($file = db_fetch_array($files_query))
        {
            if(is_file($filepath = DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename']))
            {
                unlink($filepath);
                
                $filename[] = $file['filename'];
            }

            db_delete_row('app_onlyoffice_files', $file['id']);
        } 
        
         $data = [
            'filename' => implode(',', $filename),                
            ];
            
        self::response_success($data);
    }
    
    public function download()
    {
        global $app_entities_cache, $app_fields_cache;
        
        //check value
        if(!strlen($this->attachment_value = $this->item['field_' . $this->field_id]))
        {
            api::response_error('Value is empty in field #' . $this->field_id);
        }        
                
        //check field type        
        if(!in_array($field_type = $app_fields_cache[$this->entity_id][$this->field_id]['type'], self::SUPPORTED_FIELD_TYPES))
        {
            api::response_error('Field type #' . $field_type . ' not supported to download');
        }
        
        if($field_type == 'fieldtype_onlyoffice')
        {
            $this->download_onlyoffice();
        }
        else
        {
            $this->download_attachment();
        }  
    }
    
    private function download_onlyoffice()
    {
        if(strstr($this->attachment_value,','))
        {
            $zip = new ZipArchive();
            $zip_filename = "attachments-{$this->item_id}.zip";
            $zip_filepath = DIR_FS_UPLOADS . $zip_filename;                

            //open zip archive        
            $zip->open($zip_filepath, ZipArchive::CREATE);

            //add files to archive                            
            $file_query = db_query("select * from app_onlyoffice_files where id in (" . db_input_in($this->attachment_value) . ")");
            while($file = db_fetch_array($file_query))
            {                          
                if(is_file($file_path = DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename']))
                {
                  $zip->addFile($file_path,$file['filename']);                                      
                }                                
            }
            
            $zip->close();

            //check if zip archive created
            if (!is_file($zip_filepath)) 
            {
                api::response_error("Error: cannot create zip archive in " . $zip_filepath );                
            }
            
            $data = [
                'filename' => $zip_filename,
                'content' => base64_encode(file_get_contents($zip_filepath))
            ];
            
            //delete temp zip archive file
            @unlink($zip_filepath);
            
            self::response_success($data);

              
        }
        else
        {
            $file_query = db_query("select * from app_onlyoffice_files where id in (" . db_input_in($this->attachment_value) . ")");
            $file = db_fetch_array($file_query);
            
            //print_rr($file);
            
            if(!is_file($file_path = DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename']))
            {
                api::response_error('File does not exist');
            }
            
            $data = [
                'filename' => $file['filename'],
                'content' => base64_encode(file_get_contents($file_path))
            ];
            
            self::response_success($data);
        }
    }
    
    private function download_attachment()
    {
        if(strstr($this->attachment_value,','))
        {
            $zip = new ZipArchive();
            $zip_filename = "attachments-{$this->item_id}.zip";
            $zip_filepath = DIR_FS_UPLOADS . $zip_filename;                

            //open zip archive        
            $zip->open($zip_filepath, ZipArchive::CREATE);

            //add files to archive                
            foreach(explode(',',$this->attachment_value) as $filename)
            {
              $file = attachments::parse_filename($filename);                                                                    
              if(is_file($file['file_path']))
              {
                $zip->addFile($file['file_path'],$file['name']);                                      
              }
            }

            $zip->close();

            //check if zip archive created
            if (!is_file($zip_filepath)) 
            {
                api::response_error("Error: cannot create zip archive in " . $zip_filepath );                
            }
            
            $data = [
                'filename' => $zip_filename,
                'content' => base64_encode(file_get_contents($zip_filepath))
            ];
            
            //delete temp zip archive file
            @unlink($zip_filepath);  
            
            self::response_success($data);           
        }
        else
        {
            $file = attachments::parse_filename($this->attachment_value);
            
            //print_rr($file);
            
            if(!is_file($file['file_path']))
            {
                api::response_error('File does not exist');
            }
            
            $data = [
                'filename' => $file['name'],
                'content' => base64_encode(file_get_contents($file['file_path']))
            ];
            
            self::response_success($data);
        }
    }
}
