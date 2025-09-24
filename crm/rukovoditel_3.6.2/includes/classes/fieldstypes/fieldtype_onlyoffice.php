<?php

class fieldtype_onlyoffice
{

    public $options;
    
    /*
     * Defines the type of the file for the source viewed or edited document. Must be lowercase
     * https://api.onlyoffice.com/editors/config/document#fileType
     */
    private $fileType;
                      
    function __construct()
    {
        $this->options = array('title' => TEXT_FIELDTYPE_ONLYOFFICE_TITLE);
        
        $this->fileType = ['.csv','.djvu','.doc','.docm','.docx','.docxf','.dot','.dotm','.dotx','.epub','.fb2','.fodp','.fods','.fodt','.htm','.html','.mht','.odp','.ods','.odt','.oform','.otp','.ots','.ott','.oxps','.pdf','.pot','.potm','.potx','.pps','.ppsm','.ppsx','.ppt','.pptm','.pptx','.rtf','.txt','.xls','.xlsb','.xlsm','.xlsx','.xlt','.xltm','.xltx','.xml','.xps'];                
    }

    function get_configuration()
    {
        $cfg = array();
        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_URL_TO_JS_API, 'name' => 'url_to_js_api', 'type' => 'input', 'tooltip' => '<code>https://documentserver/web-apps/apps/api/documents/api.js</code><br>' . TEXT_ONLYOFFICE_API_JS_INFO, 'params' => array('class' => 'form-control input-xlarge required'));        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_SECRET_KEY . ' (token)', 'name' => 'secret_key', 'type' => 'input', 'tooltip' =>  TEXT_ONLYOFFICE_SECRET_KEY_INFO, 'params' => array('class' => 'form-control input-large required'));        
        
        $choices = [
            '' => TEXT_VIEW_ONLY_ACCESS,
            'users_view_access' => TEXT_USERS_WITH_VIEW_ACCESS,
            'users_edit_access' => TEXT_USERS_WITH_EDIT_ACCESS,
            'assigned_users' => TEXT_ASSIGNED_USERS
        ];
        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_ALLOW_EDIT_DOCUEMENT, 'name' => 'allow_edit', 'type' => 'dropdown','choices'=>$choices, 'params' => array('class' => 'form-control input-xlarge','onChange' => 'fields_types_ajax_configuration(\'get_assigned_users_fields\',this.value)'));        

        $cfg[TEXT_SETTINGS][] = array('name' => 'get_assigned_users_fields', 'type' => 'ajax', 'html' => '<script>fields_types_ajax_configuration(\'get_assigned_users_fields\',$("#fields_configuration_allow_edit").val())</script>');
        
        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_LANGUAGE, 'name' => 'lang', 'type' => 'input','default'=>'en','tooltip'=>TEXT_EXAMPLE . ': en, ru, it. <a href="https://api.onlyoffice.com/editors/config/editor#lang" target="_blank">' . TEXT_READ_MORE. '</a>', 'params' => array('class' => 'form-control input-small','maxlength'=>5));        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_LOCATION, 'name' => 'location', 'type' => 'input','default'=>'en','tooltip'=>TEXT_EXAMPLE . ': en, ru, ca. <a href="https://api.onlyoffice.com/editors/config/editor#location" target="_blank">' . TEXT_READ_MORE. '</a>', 'params' => array('class' => 'form-control input-small','maxlength'=>2));        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_REGION, 'name' => 'region', 'type' => 'input','default'=>'en-US','tooltip'=>TEXT_EXAMPLE . ': en-US, fr-FR. <a href="https://api.onlyoffice.com/editors/config/editor#region" target="_blank">' . TEXT_READ_MORE. '</a>', 'params' => array('class' => 'form-control input-small','maxlength'=>5));

        $cfg[TEXT_EXTRA][] = array('title' => TEXT_NOTIFY_WHEN_CHANGED, 'name' => 'notify_when_changed', 'type' => 'checkbox', 'tooltip_icon' => TEXT_NOTIFY_WHEN_CHANGED_TIP);
        $cfg[TEXT_EXTRA][] = array('title' => TEXT_ALLOW_SEARCH, 'name' => 'allow_search', 'type' => 'checkbox', 'tooltip_icon' => TEXT_ALLOW_SEARCH_TIP);
        
        
        $cfg[TEXT_EXTRA][] = array('title' => TEXT_ALLOW_CHANGE_FILE_NAME, 'name' => 'allow_change_file_name', 'type' => 'checkbox');
        $cfg[TEXT_EXTRA][] = array('title' => TEXT_FILES_UPLOAD_LIMIT, 'name' => 'upload_limit', 'type' => 'input', 'tooltip_icon' => TEXT_FILES_UPLOAD_LIMIT_TIP, 'params' => array('class' => 'form-control input-xsmall'));
        $cfg[TEXT_EXTRA][] = array('title' => TEXT_FILES_UPLOAD_SIZE_LIMIT, 'name' => 'upload_size_limit', 'type' => 'input', 'tooltip_icon' => TEXT_FILES_UPLOAD_SIZE_LIMIT_TIP, 'tooltip' => TEXT_MAX_UPLOAD_FILE_SIZE . ' ' . CFG_SERVER_UPLOAD_MAX_FILESIZE . 'MB ' . TEXT_MAX_UPLOAD_FILE_SIZE_TIP, 'params' => array('class' => 'form-control input-xsmall'));
        
        $choices = [];
        foreach($this->fileType as $v)
        {
           $choices[$v] = $v; 
        }
        $cfg[TEXT_EXTRA][] = array('title' => TEXT_ALLOWED_EXTENSIONS, 'name' => 'allowed_extensions', 'type' => 'dropdown','choices'=>$choices, 'params' => array('class' => 'form-control input-xlarge chosen-select','multiple'=>'multiple'));        
                
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
        
        $tooltip = TEXT_ENTER_TEXT_PATTERN_INFO_SHORT . '<br>' . TEXT_EXAMPLE . ': <code>myfile_[221]_[current_date_time]</code>' ;
        $cfg[TEXT_DISPLAY][] = array('title' => TEXT_FILENAME_TEMPLATE, 'name' => 'filename_template', 'type' => 'input', 'params' => array('class' => 'form-control input-larege'),'tooltip'=>$tooltip,'tooltip_icon'=>TEXT_FIELDTYPE_ATTACHMENTS_FILENAME_TEMPLATE_NOTE);

       
        $cfg[TEXT_DELETION][] = array('title' => TEXT_ALLOWS_DELETE_IF_HAS_DELETE_ACCESS, 'name' => 'check_delete_access', 'type' => 'checkbox');

        return $cfg;
    }
    
     function get_ajax_configuration($name, $value)
    {
        $cfg = array();
        
        switch ($name)
        {
            case 'get_assigned_users_fields':
                
                if($value=='assigned_users')
                {
                    $choices = [];
                    $field_query = db_query("select id, name, type, configuration from app_fields where type in (" . fields_types::get_types_for_assigned_users_list(). ") and entities_id=" . _POST('entities_id'));
                    while($field = db_fetch_array($field_query))
                    {
                        $choices[$field['id']] = fields::get_name($field);
                    }
                    
                    $cfg[] = array(
                            'title' => TEXT_USERS,
                            'name' => 'assigned_users_fields',
                            'type' => 'dropdown',
                            'choices' => $choices,
                            'tooltip' => TEXT_SPECIFY_REQUIRED_FIELDS,
                            'params' => array('class' => 'form-control input-xlarge chosen-select', 'multiple' => 'multiple'),
                        );
                    
                }                
                
                break;
        }

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
            $uploadScript = url_for('users/registration', 'action=onlyoffice_upload&field_id=' . $field_id, true);
            $previewScript = url_for('users/registration', 'action=onlyoffice_preview&field_id=' . $field_id . '&form_token=' . $form_token);
            $delete_file_url = url_for('users/registration', 'action=onlyoffice_delete', true);
        }
        elseif($app_items_form_name == 'public_form' or (isset($_GET['form_name'])) and $_GET['form_name'] == 'public_form')
        {
            $public_form['id'] = isset($_GET['public_form_id']) ? _GET('public_form_id') : $public_form['id'];
            $form_token = md5($app_session_token . $timestamp);
            $uploadScript = url_for('ext/public/form', 'action=onlyoffice_upload&id=' . $public_form['id'] . '&field_id=' . $field_id, true);
            $previewScript = url_for('ext/public/form', 'action=onlyoffice_preview&field_id=' . $field_id . '&id=' . $public_form['id'] . '&form_token=' . $form_token, true);
            $delete_file_url = url_for('ext/public/form', 'action=onlyoffice_delete&id=' . $public_form['id'] , true);
        }
        elseif($app_items_form_name == 'account_form')
        {
            $item_path = $field['entities_id'] . (strlen($obj['id']) ? '-' . $obj['id'] : '');
            $form_token = md5($app_user['id'] . $timestamp);
            $uploadScript = url_for('users/account', 'action=onlyoffice_upload&path=' . $item_path . '&field_id=' . $field_id, true);
            $previewScript = url_for('users/account', 'action=onlyoffice_preview&field_id=' . $field_id . '&path=' . $item_path . '&form_token=' . $form_token);
            $delete_file_url = url_for('users/account', 'action=onlyoffice_delete&path=' . $item_path);
        }        
        else
        {
            $item_path = $field['entities_id'] . (strlen($obj['id']) ? '-' . $obj['id'] : '');
            $form_token = md5($app_user['id'] . $timestamp);
            $uploadScript = url_for('items/onlyoffice', 'action=upload&path=' . $item_path . '&field_id=' . $field_id, true);
            $previewScript = url_for('items/onlyoffice', 'action=preview&field_id=' . $field_id . '&path=' . $item_path . '&form_token=' . $form_token);
            $delete_file_url = url_for('items/onlyoffice', 'action=delete&path=' . $item_path);
        }
                

        $uploadLimit = (strlen($cfg->get('upload_limit')) ? (int) $cfg->get('upload_limit') : 0);
        $onComplateAction = ($uploadLimit > 0 ? 'onUploadComplete' : 'onQueueComplete');

        $allowed_extensions = is_array($cfg->get('allowed_extensions')) ? $cfg->get('allowed_extensions') : $this->fileType;

        if(isset($params['is_new_item']) and !$params['is_new_item'])
        {
            $attachments_preview_html = (new onlyoffice($field['entities_id']))->preview($field['id'],'',$obj['id']);
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
                "' . $onComplateAction . '" : function(file, data) {
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
                $file_info_query = db_query("select * from app_onlyoffice_files where id={$id}");
                if(!$file_info = db_fetch_array($file_info_query)) continue;
                
                $attachments[] = $id;
                
                $filepathinfo = pathinfo($file_info['filename']);
                
                if($cfg->get('allow_change_file_name') and in_array($app_module_path, ['items/form', 'items/items','items/processes']) and isset($file['name']) and $file['name']!=$filepathinfo['filename'])
                {
                    $new_filename = $file['name'] . '.' . $filepathinfo['extension'];
                    
                    if(rename(DIR_WS_ONLYOFFICE . $file_info['folder'] . '/' . $file_info['filename'],DIR_WS_ONLYOFFICE . $file_info['folder'] . '/' . $new_filename))
                    {                        
                        $sql_data = [
                            'filename' => $new_filename,                        
                        ];

                        db_perform('app_onlyoffice_files', $sql_data,'update','id=' . $id);
                    }
                }
                
            }

            //print_rr($attachments);        
            //exit();
        }
        else
        {
            $attachments = [];
        }
        
        $options['value'] = implode(',', $attachments);
        
        //reset token
        if(count($attachments)>0)
        {            
            db_query("update app_onlyoffice_files set form_token='' where id in (" . db_input_in($attachments) . ")");
        }
                       
        //delete not assigned files
        $file_query = db_query("select * from app_onlyoffice_files where length(form_token)>0 and date_added<" . strtotime("-1 day"),false);
        while($file = db_fetch_array($file_query))
        {
            if(is_file($filepath = DIR_WS_ONLYOFFICE . $file['folder'] . '/' . $file['filename']))
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
        
        $files_query = db_query("select * from app_onlyoffice_files where field_id='" . db_input($options['field']['id']) . "' and id in (" . db_input_in($options['value']) . ") {$order_sql}", false);
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
            $file_info = onlyoffice::get_file_info($file);
            
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
                $link = link_to($croped_name, url_for('items/onlyoffice_editor', 'path=' . $path . '&action=open&field=' . $options['field']['id']. '&file=' . $file['id']),['target'=>"_blank"]);
                $link .= ' ' . link_to('<i class="fa fa-download"></i>', url_for('items/onlyoffice', 'path=' . $path . '&action=download&file=' . $file['id']));
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

   

    

   

}
