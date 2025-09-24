<?php

require('includes/libs/FileStorage/Dropbox/1.22.0/vendor/autoload.php');
require_once 'includes/libs/FileStorage/Dropbox/1.22.0/vendor/spatie/dropbox-api/src/AutoRefreshingDropBoxTokenService.php';

class fieldtype_dropbox
{

    public $options;
                                
    function __construct()
    {
        $this->options = array('title' => TEXT_FIELDTYPE_DROPBOX_TITLE);
                
    }
    
   public function getClassName()
   {
      return static::class;
   }

    function get_configuration()
    {
        $cfg = array();
        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_ROOT_FOLDER , 'name' => 'root_folder', 'default'=>strtoupper($_SERVER['HTTP_HOST']),'type' => 'input', 'tooltip' =>  TEXT_ROOT_FOLDER_FILE_STORAGE_WARN, 'params' => array('class' => 'form-control input-large required'));                
        
        $tooltip = '<a href="https://www.dropbox.com/developers/apps" target="_blank">https://www.dropbox.com/developers/apps</a>';
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_APP_KEY , 'name' => 'app_key', 'type' => 'input', 'tooltip' =>  $tooltip, 'params' => array('class' => 'form-control input-large required'));                
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_SECRET_KEY , 'name' => 'app_secret', 'type' => 'input', 'tooltip' =>  '', 'params' => array('class' => 'form-control input-large required'));                
        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_REDIRECT_URI , 'name' => 'redirect_uri', 'default'=> url_for('dahsboard/dashboard'),'type' => 'input_readonly', 'tooltip' =>  '', 'params' => array('class' => 'form-control select-all'));                
        
        $tooltip = '
            <a href="https://www.dropbox.com/oauth2/authorize?token_access_type=offline&response_type=code&client_id=" onClick="return dropbox_get_app_code(this)" target="_blank" class="btn btn-default">' . TEXT_GET_VALUE . '</a>
            <script>
                function dropbox_get_app_code(link)
                {
                    let app_key = $("#fields_configuration_app_key").val()
                    
                    if(app_key.length==0)
                    {
                        var validator = $( "#fields_form" ).validate();
                        validator.element( "#fields_configuration_app_key" );
                        //alert("Please enter app key!")
                        return false
                    }
                    else
                    {
                        link.href = link.href+app_key
                        return true
                    }
                }           
            </script>            
            ';
        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_APP_CODE , 'name' => 'app_code', 'type' => 'input', 'tooltip' =>  $tooltip, 'params' => array('class' => 'form-control required'));   
        
        $tooltip = '<a href="javascript: dropbox_get_refresh_token()" class="btn btn-default">' . TEXT_GET_VALUE . '</a>
            <script>
                function dropbox_get_refresh_token()
                {             
                    var validator = $( "#fields_form" ).validate();
                    
                    if(validator.element( "#fields_configuration_app_key" ) && validator.element( "#fields_configuration_app_secret" ) && validator.element( "#fields_configuration_app_code" ))
                    {
                        
                        let app_code = $("#fields_configuration_app_code").val()
                        let app_key = $("#fields_configuration_app_key").val()
                        let app_secret = $("#fields_configuration_app_secret").val()

                        $.ajax({
                            url: url_for("fieldtype/dropbox","action=get_refresh_token"),
                            type: "POST",
                            data: {
                                app_code: app_code,
                                app_key: app_key,
                                app_secret: app_secret
                            }
                        }).done(function(msg){
                            data = JSON.parse(msg)

                            if ("error" in data)
                            {
                              alert(JSON.stringify(data))
                              $("#fields_configuration_refresh_token").val("")
                            }
                            else
                            {
                                $("#fields_configuration_refresh_token").val(data.refresh_token).trigger("change")
                            }
                        })
                    }
                    
                    return false
                }
            </script>
            ';
        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_APP_TOKEN , 'name' => 'refresh_token', 'type' => 'input', 'tooltip' =>  $tooltip, 'params' => array('class' => 'form-control required','readonly'=>'readonly')); 
                                                
        $cfg[TEXT_EXTRA][] = array('title' => TEXT_NOTIFY_WHEN_CHANGED, 'name' => 'notify_when_changed', 'type' => 'checkbox', 'tooltip_icon' => TEXT_NOTIFY_WHEN_CHANGED_TIP);
        $cfg[TEXT_EXTRA][] = array('title' => TEXT_ALLOW_SEARCH, 'name' => 'allow_search', 'type' => 'checkbox', 'tooltip_icon' => TEXT_ALLOW_SEARCH_TIP);
        
                
        $cfg[TEXT_EXTRA][] = array('title' => TEXT_FILES_UPLOAD_LIMIT, 'name' => 'upload_limit', 'type' => 'input', 'tooltip_icon' => TEXT_FILES_UPLOAD_LIMIT_TIP, 'params' => array('class' => 'form-control input-xsmall'));
        $cfg[TEXT_EXTRA][] = array('title' => TEXT_FILES_UPLOAD_SIZE_LIMIT, 'name' => 'upload_size_limit', 'type' => 'input', 'tooltip_icon' => TEXT_FILES_UPLOAD_SIZE_LIMIT_TIP, 'tooltip' => TEXT_MAX_UPLOAD_FILE_SIZE . ' ' . CFG_SERVER_UPLOAD_MAX_FILESIZE . 'MB ' . TEXT_MAX_UPLOAD_FILE_SIZE_TIP, 'params' => array('class' => 'form-control input-xsmall'));
               
        $cfg[TEXT_EXTRA][] = array('title' => TEXT_ALLOWED_EXTENSIONS, 'name' => 'allowed_extensions', 'type' => 'input', 'tooltip_icon' => TEXT_ALLOWED_EXTENSIONS_TIP, 'params' => array('class' => 'form-control input-large'));
                
        $cfg[TEXT_DISPLAY][] = array('title' => TEXT_DISPLAY_FILE_DATE_ADDED, 'name' => 'display_date_added', 'type' => 'checkbox');
        
        $cfg[TEXT_DISPLAY][] = array(
            'title' => TEXT_ATTACHMENTS_SORT_ORDER, 
            'name' => 'allow_sort_order', 
            'choices' =>[
                '' => TEXT_BY_DATE_UPLOAD,
                'sorting_by_filename' => TEXT_BY_FILENAME,
                'manual_sorting' => TEXT_MANUAL_SORTING
            ],
            'type' => 'dropdown', 
            'params' => ['class'=>'form-control input-large'],
            'tooltip' => '<span form_display_rules="fields_configuration_allow_sort_order:manual_sorting">' . TEXT_ALLOW_SORT_ORDER_ATTACHMENTS_TIP . '</span>');
                
        $cfg[TEXT_DISPLAY][] = array('title' => TEXT_HIDE_FIELD_IF_EMPTY, 'name' => 'hide_field_if_empty', 'type' => 'checkbox', 'tooltip_icon' => TEXT_HIDE_FIELD_IF_EMPTY_TIP);
                       
        $cfg[TEXT_DELETION][] = array('title' => TEXT_ALLOWS_DELETE_IF_HAS_DELETE_ACCESS, 'name' => 'check_delete_access', 'type' => 'checkbox');

        return $cfg;
    }
    

    function render($field, $obj, $params = array())
    {
        global $uploadify_attachments, $uploadify_attachments_queue, $current_path, $app_user, $app_items_form_name, $public_form, $app_session_token, $app_path;

        if(!isset($field['configuration']))
            $field['configuration'] = '';

        $cfg = new fields_types_cfg($field['configuration']);

        $field_id = $field['id'];

        $uploadify_attachments[$field_id] = array();
        $uploadify_attachments_queue[$field_id] = array();

        if(strlen($obj['field_' . $field['id']]??'') > 0)
        {
            $uploadify_attachments[$field_id] = explode(',', $obj['field_' . $field['id']]);
        }

        $timestamp = time();

        $delete_file_url = '';

        if($app_items_form_name == 'registration_form')
        {
            $form_token = md5($app_session_token . $timestamp);
            $uploadScript = url_for('users/registration', 'action=fs_upload&ft=' . $this->getClassName() . '&field_id=' . $field_id, true);
            $previewScript = url_for('users/registration', 'action=fs_preview&ft=' . $this->getClassName() . '&field_id=' . $field_id . '&form_token=' . $form_token);
            $delete_file_url = url_for('users/registration', 'action=fs_delete&ft=' . $this->getClassName() . '&field_id=' . $field_id, true);
        }
        elseif($app_items_form_name == 'public_form' or (isset($_GET['form_name'])) and $_GET['form_name'] == 'public_form')
        {
            $public_form['id'] = isset($_GET['public_form_id']) ? _GET('public_form_id') : $public_form['id'];
            $form_token = md5($app_session_token . $timestamp);
            $uploadScript = url_for('ext/public/form', 'action=fs_upload&ft=' . $this->getClassName() . '&id=' . $public_form['id'] . '&field_id=' . $field_id, true);
            $previewScript = url_for('ext/public/form', 'action=fs_preview&ft=' . $this->getClassName() . '&field_id=' . $field_id . '&id=' . $public_form['id'] . '&form_token=' . $form_token, true);
            $delete_file_url = url_for('ext/public/form', 'action=fs_delete&ft=' . $this->getClassName() . '&id=' . $public_form['id'] . '&field_id=' . $field_id, true);
        }
        elseif($app_items_form_name == 'account_form')
        {
            $item_path = $field['entities_id'] . (strlen($obj['id']) ? '-' . $obj['id'] : '');
            $form_token = md5($app_user['id'] . $timestamp);
            $uploadScript = url_for('users/account', 'action=fs_upload&ft=' . $this->getClassName() . '&path=' . $item_path . '&field_id=' . $field_id, true);
            $previewScript = url_for('users/account', 'action=fs_preview&ft=' . $this->getClassName() . '&field_id=' . $field_id . '&path=' . $item_path . '&form_token=' . $form_token);
            $delete_file_url = url_for('users/account', 'action=fs_delete&ft=' . $this->getClassName() . '&path=' . $item_path. '&field_id=' . $field_id);
        }        
        else
        {
            $item_path = $field['entities_id'] . (strlen($obj['id']) ? '-' . $obj['id'] : '');
            $form_token = md5($app_user['id'] . $timestamp);
            $uploadScript = url_for('items/file_storage', 'action=fs_upload&ft=' . $this->getClassName() . '&path=' . $item_path . '&field_id=' . $field_id, true);
            $previewScript = url_for('items/file_storage', 'action=fs_preview&ft=' . $this->getClassName() . '&field_id=' . $field_id . '&path=' . $item_path . '&form_token=' . $form_token);
            $delete_file_url = url_for('items/file_storage', 'action=fs_delete&ft=' . $this->getClassName() . '&path=' . $item_path . '&field_id=' . $field_id);
        }
                

        $uploadLimit = (strlen($cfg->get('upload_limit')) ? (int) $cfg->get('upload_limit') : 0);
        $onComplateAction = ($uploadLimit > 0 ? 'onUploadComplete' : 'onQueueComplete');

        $allowed_extensions = strlen($cfg->get('allowed_extensions')) ? explode(',',$cfg->get('allowed_extensions')) : [];
        $allowed_extensions  = array_map(function($v){ return '.' . trim($v);},$allowed_extensions);

        if(isset($params['is_new_item']) and !$params['is_new_item'])
        {            
            $attachments_preview_html =  file_storage_field::preview($field['entities_id'],$field['id'],'',$obj['id']); 
        }
        else
        {
            $attachments_preview_html = '';
        }
        

        $html = '
      <div class="form-control-static"> 
        <input style="cursor: pointer" type="file" name="uploadifive_attachments_upload_' . $field_id . '" id="uploadifive_attachments_upload_' . $field_id . '" /> 
      </div>
      
      <div id="uploadifive_queue_list_' . $field_id . '"></div>
      <div id="uploadifive_attachments_list_' . $field_id . '" data-delete_url = "' . $delete_file_url . '">
        ' . $attachments_preview_html . '        
      </div>
      
      <script type="text/javascript">
		
      var is_file_uploading = null;  
        		
        function uploadifive_oncomplate_filed_' . $field_id . '()
        {
            is_file_uploading = null  
            
            $(".uploadifive-queue-item.complete").fadeOut()
            
            $("#uploadifive_attachments_list_' . $field_id . '").append("<div class=\"loading_data\"></div>")
            $("#uploadifive_attachments_list_' . $field_id . '").load("' . $previewScript . '")
        }		
      
        $(function() {
        
            $("#uploadifive_attachments_upload_' . $field_id . '").uploadifive({
                "auto"             : true,  
                "dnd"              : false, 
                "fileType"         :   [\'' . implode(',',$allowed_extensions) . '\'], 
                "fileTypeExtra"	   :   "' . implode(',',array_map(function($v){ return substr($v,1);},$allowed_extensions) ). '",
                "buttonClass"      : "btn btn-default btn-upload",
                "buttonText"       : "<i class=\"fa fa-upload\"></i> ' . TEXT_ADD_ATTACHMENTS . '",				
                "formData"         : {
                                        "timestamp" : ' . $timestamp . ',
                                        "token"     : "' . $form_token . '",
                                        "form_session_token" : "' . $app_session_token . '",
                                        "app_form_name": "' . $app_items_form_name . '",                                
                                        "filename_template": "' . ($cfg->get('upload_limit')==1 ? addslashes($cfg->get('filename_template')) : '') . '" 
                                     },
                "queueID"          : "uploadifive_queue_list_' . $field_id . '",
                "fileSizeLimit" : "' . (strlen($cfg->get('upload_size_limit')) ? (int) $cfg->get('upload_size_limit') : CFG_SERVER_UPLOAD_MAX_FILESIZE) . 'MB",
                "queueSizeLimit" : ' . $uploadLimit . ',
                "uploadScript"     : "' . $uploadScript . '",
                "onUpload"         :  function(filesToUpload){
                  is_file_uploading = true;  					
                },
                onUploadComplete: function(file,data)
                {                    
                    if(data.length>0)
                    {
                        alert(data)
                    }
                },
                "' . $onComplateAction . '" : function(file, data) {
                                        
                    if(isset(data) && data.length>0)
                    {
                        alert(data)
                    }
                    
                    uploadifive_oncomplate_filed_' . $field_id . '()
                },
                "onError":function(errorType) {
                     is_file_uploading = null;             
                },
                "onCancel"     : function() { 	
                     is_file_uploading = null;  				
                } 		
            });
                        
        $("button[type=submit]").bind("click",function(){                                                 
            if(is_file_uploading)
            {
              alert("' . TEXT_PLEASE_WAYIT_FILES_LOADING . '"); return false;
            }                           
          });
        
  		});
	</script>
    ';
                
        return $html;
    }

    function process($options)
    {
        global $app_changed_fields,$app_module_path;

        $cfg = new settings($options['field']['configuration']??'');
        
        if(is_array($options['value']))
        {
            $attachments = [];

            //print_rr($options['value']);

            foreach($options['value'] as $id=>$file)
            {
                $file_info_query = db_query("select * from app_file_storage where id={$id}");
                if(!$file_info = db_fetch_array($file_info_query)) continue;
                
                $attachments[] = $id;                                                
            }

            //print_rr($attachments);        
            //exit();
        }
        else
        {
            $attachments = [];
        }
        
        $options['value'] = implode(',', $attachments);
        
        //print_rr($attachments);        
        //exit();
        
        
        //reset token
        if(count($attachments)>0)
        {            
            db_query("update app_file_storage set form_token='' where id in (" . db_input_in($attachments) . ")");
        }
                       
        //delete not assigned files
        $file_query = db_query("select * from app_file_storage where length(form_token)>0 and date_added<" . strtotime("-1 day"),false);
        while($file = db_fetch_array($file_query))
        {
            if(is_file($filepath = DIR_WS_FILE_STORAGE . $file['id'] . '/' . $file['filename']))
            {
                unlink($filepath);
            }                        
            
            db_delete_row('app_onlyoffice_files', $file['id']);
        }                        
                   
        //notify when changed        
        if(isset($options['is_new_item']) and !$options['is_new_item'])
        {
            $cfg = new fields_types_cfg($options['field']['configuration']);

            if($options['value'] != $options['current_field_value'] and $cfg->get('notify_when_changed') == 1)
            {
                $app_changed_fields[] = array(
                    'name' => $options['field']['name'],
                    'value' => (strlen($options['value']) ? count(explode(',', $options['value'])) : 0),
                    'fields_id' => $options['field']['id'],
                    'fields_value' => $options['value'],
                );
            }
        }

        return $options['value'];
    }
  
    function output($options)
    {
        if(!strlen($options['value']??'')) return '';
        
        $options_cfg = new fields_types_options_cfg($options);
        $cfg = new fields_types_cfg((isset($options['field']['configuration']) ? $options['field']['configuration'] : ''));

        
        $attachments = [];
                     
        switch($cfg->get('allow_sort_order'))
        {
            case 'sorting_by_filename':
                $order_sql = " order by filename";
                break;
            case 'manual_sorting':
                $order_sql = " order by sort_order, id";
                break;
            default:
                $order_sql = " order by id";
                break;
        }
        
        $files_query = db_query("select * from app_file_storage where field_id='" . db_input($options['field']['id']) . "' and id in (" . db_input_in($options['value']) . ") {$order_sql}", false);
        while ($file = db_fetch_array($files_query))
        {
            $attachments[] = $file;
        }
        
        if(!count($attachments)) return '';
        
        if(isset($options['is_export']) or isset($options['is_public_form']))
        {
            $list = [];
            foreach($attachments as $file)
            {
                $croped_name = app_crop_str($file['filename']);
                $list[] = $croped_name;
            }
            
            return implode(', ', $list);
        }
                
        
        $html = ' 		
            <ul class="attachments" style="padding: 0px; margin: 0px;">';
        
        foreach($attachments as $file)
        {
            $croped_name = app_crop_str($file['filename']);
            $file_info = file_storage_field::get_file_info($file);
            
                        
            if(isset($options['is_email']) and $options['is_email']==true)
            {
                $link = $croped_name . '  <small>(' . $file_info['size'] . ')' . self::add_file_date_added($file, $cfg) . '</small>';
                $html .= '
                    <li style="margin-left: 20px;">' . $link . '</li>
                  ';
            }
            else
            {
                $path = $options['field']['entities_id'] . '-' . $options['item']['id'];
                
                $download_url = url_for('items/file_storage', 'path=' . $path . '&action=fs_download&ft=' . $this->getClassName() . '&field_id=' . $options['field']['id']. '&file=' . $file['id']);
                                
                $link = link_to($croped_name, $download_url,['target'=>'_blank']);
                $link .= ' ' . link_to('<i class="fa fa-download"></i>', $download_url,['target'=>'_blank']);
                $link .= '  <small>(' . $file_info['size'] . ')' . self::add_file_date_added($file, $cfg) . '</small>';
                $html .= '
                    <li style="list-style-image: url(' . url_for_file($file_info['icon']) . '); margin-left: 20px;">' . $link . '</li>
                  ';
            }
        }
        
        $html .= '</ul>';
        
        //print_rr($attachments);
        
        return $html;
    }

    static function add_file_date_added($file, $cfg)
    {
        if($cfg->get('display_date_added') == 1)
        {
            return ' - ' . format_date_time($file['date_added']);
        }
        else
        {
            return '';
        }
    }

   
    function upload($entity_id, $field_id)
    {
        global $app_fields_cache;
        
        if($file_id = file_storage_field::upload($entity_id,$field_id))
        {
            $file_info = db_query("select * from app_file_storage where id={$file_id}");
            if($file = db_fetch_array($file_info))
            {
                $cfg = new settings($app_fields_cache[$entity_id][$field_id]['configuration']);
                
                if(is_file($filepath = DIR_FS_FILE_STORAGE . $file['id'] . '/' . $file['filename']))
                {                   
                    try
                    {
                        $tokenProvider = new App\Services\AutoRefreshingDropBoxTokenService($cfg->get('app_key'), $cfg->get('app_secret'), $cfg->get('refresh_token'));
                        $client = new Spatie\Dropbox\Client($tokenProvider);                         
                        $content = file_get_contents($filepath);
                        $upload_to = $cfg->get('root_folder') . '/' . $file['folder'] .'/' . $file['filename'];                      
                        $client->upload($upload_to, $content, 'overwrite');
                    }
                    catch(Exception $e)
                    {
                       echo 'DROPBOX ERROR: ' . TEXT_ERROR_LOADING_DATA . '. ' . TEXT_CHECK_FIELD_SETTINGS;
                           
                       //remove record in db if error
                       db_delete_row('app_file_storage', $file['id']);
                    }   
                    
                    //remove uploaded file
                    unlink($filepath);
                    rmdir(DIR_FS_FILE_STORAGE .$file['id']);                    
                }
            }
        }
    }
    
    function delete($entity_id, $field_id, $file_id)
    {
        global $app_fields_cache;
        
        $cfg = new settings($app_fields_cache[$entity_id][$field_id]['configuration']);
                    
        $tokenProvider = new App\Services\AutoRefreshingDropBoxTokenService($cfg->get('app_key'), $cfg->get('app_secret'), $cfg->get('refresh_token'));
        $client = new Spatie\Dropbox\Client($tokenProvider);                         
                        
        //delete in storage
        $file_query = db_query("select * from app_file_storage where entity_id={$entity_id} and field_id={$field_id} and id in (" . db_input_in($file_id) . ")");
        while($file = db_fetch_array($file_query))
        {
            $delete_from = $cfg->get('root_folder') . '/' . $file['folder'] .'/' . $file['filename'];  

            try
            {
                $client->delete($delete_from);                                
            }
            catch(Exception $e)
            {
               $client = false; 
            }
        }                       
        
        //delete in table
        db_query("delete from app_file_storage where entity_id={$entity_id} and field_id={$field_id} and id in (" . db_input_in($file_id) . ")");
                                        
    }
    
    function download($entity_id,$item_id, $field_id, $file_id)
    {
        global $app_fields_cache;
        
        $cfg = new settings($app_fields_cache[$entity_id][$field_id]['configuration']);
        
        $file_query = db_query("select * from app_file_storage where entity_id={$entity_id} and field_id={$field_id} and id={$file_id}");
        if($file = db_fetch_array($file_query))
        {
            
            $tokenProvider = new App\Services\AutoRefreshingDropBoxTokenService($cfg->get('app_key'), $cfg->get('app_secret'), $cfg->get('refresh_token'));
            $client = new Spatie\Dropbox\Client($tokenProvider);                         

            $path = $cfg->get('root_folder') . '/' . $file['folder'] .'/' . $file['filename'];            
            
            try{
                $link = $client->getTemporaryLink($path);
            }
            catch(Exception $e)
            {
                echo $path . '<br>';
                die('DROPBOX ERROR: ' . $e->getMessage());
            }
                        
            header('Location: ' . $link);
            exit();                       	
        }
        
    }             

}
