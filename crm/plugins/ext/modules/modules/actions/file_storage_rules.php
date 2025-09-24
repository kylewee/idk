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

switch($app_module_action)
{
  case 'save':
          	
      $sql_data = array(      
      	'modules_id' => $_POST['modules_id'],
      	'entities_id' => $_POST['entities_id'],                                                     	
      	'fields'=> (isset($_POST['fields']) ? implode(',', $_POST['fields']) : ''),      	      	
      );
                                                                                    
      if(isset($_GET['id']))
      {        
        db_perform('app_ext_file_storage_rules',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");       
      }
      else
      {                               
        db_perform('app_ext_file_storage_rules',$sql_data);                    
      }
      
      redirect_to('ext/modules/file_storage_rules');
      
    break;
  case 'delete':
            
  		if(isset($_GET['id']))
  		{
      	db_delete_row('app_ext_file_storage_rules',$_GET['id']);
  		}
                 
      redirect_to('ext/modules/file_storage_rules');
    break;      
  case 'get_entities_fields':
      
        $entities_id = _post::int('entities_id');
        
        $obj = array();

        if(isset($_POST['id']))
        {
          $obj = db_find('app_ext_file_storage_rules',$_POST['id']);  
        }
        else
        {
          $obj = db_show_columns('app_ext_file_storage_rules');
        }
               
        $choices = [];
        $fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type in ('fieldtype_input_file','fieldtype_attachments') and f.entities_id='" . $entities_id . "' and f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");
        while($fields = db_fetch_array($fields_query))
        {
        	$choices[$fields['id']] = $fields['name'];
        }
         
        $html = '
        			<div class="form-group" style="margin-top: 30px;">
						  	<label class="col-md-3 control-label" for="cfg_sms_send_to_record_number">' .  TEXT_FIELD . '</label>
						    <div class="col-md-9">
						  	  ' . select_tag('fields[]',$choices, $obj['fields'],array('class'=>'form-control input-large chosen-select required','multiple'=>'multiple')) . '
						  	  ' . tooltip_text(TEXT_AVAILABLE_FIELDS . ': ' . TEXT_FIELDTYPE_INPUT_FILE_TITLE . ', ' . TEXT_FIELDTYPE_ATTACHMENTS_TITLE) . '
						    </div>
						  </div>
        			';
        
        echo $html;
        
      exit();
    break;
}