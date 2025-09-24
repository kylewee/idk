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


if(!app_session_is_registered('xml_templates_filter'))
{
    $xml_templates_filter = 0;
    app_session_register('xml_templates_filter');
}

switch($app_module_action)
{
    case 'set_xml_templates_filter':
        $xml_templates_filter = $_POST['xml_templates_filter'];

        redirect_to('ext/xml_export/templates');
        break;
    case 'save':
        $sql_data = array(
            'name' => $_POST['name'],
            'template_filename' => $_POST['template_filename'],
            'transliterate_filename' => (isset($_POST['transliterate_filename']) ? 1 : 0),
            'entities_id' => $_POST['entities_id'],
            'button_title' => $_POST['button_title'],
            'button_position' => (isset($_POST['button_position']) ? implode(',', $_POST['button_position']) : ''),
            'button_color' => $_POST['button_color'],
            'button_icon' => $_POST['button_icon'],
            'is_active' => (isset($_POST['is_active']) ? 1 : 0),
            'sort_order' => $_POST['sort_order'],
            'users_groups' => (isset($_POST['users_groups']) ? implode(',', $_POST['users_groups']) : ''),
            'assigned_to' => (isset($_POST['assigned_to']) ? implode(',', $_POST['assigned_to']) : ''),
            'template_header' => $_POST['template_header'],
            'template_body' => $_POST['template_body'],
            'template_footer' => $_POST['template_footer'],
            'is_public' => $_POST['is_public'],
            'related_entities_template' => isset($_POST['related_entities']) ? json_encode($_POST['related_entities']) : '',
        );

        if(isset($_GET['id']))
        {
            db_perform('app_ext_xml_export_templates', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_ext_xml_export_templates', $sql_data);
        }

        redirect_to('ext/xml_export/templates');
        break;
    case 'save_group':
        $sql_data = array(
            'name' => $_POST['name'],
            'entities_id' => 0,
            'is_active' => (isset($_POST['is_active']) ? 1 : 0),
            'sort_order' => $_POST['sort_order'],
            'template_header' => $_POST['template_header'],
            'template_body' => (isset($_POST['template_body']) ? implode(',', $_POST['template_body']) : ''),
            'template_footer' => $_POST['template_footer'],
            'is_public' => 1,
        );

        if(isset($_GET['id']))
        {
            db_perform('app_ext_xml_export_templates', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_ext_xml_export_templates', $sql_data);
        }

        redirect_to('ext/xml_export/templates');
        break;
    case 'delete':
        if(isset($_GET['id']))
        {
            db_query("delete from app_ext_xml_export_templates where id='" . db_input($_GET['id']) . "'");

            $report_info_query = db_query("select * from app_reports where reports_type like '%xml_export" . db_input($_GET['id']) . "%'");
            while($report_info = db_fetch_array($report_info_query))
            {
                reports::delete_reports_by_id($report_info['id']);
            }

            $alerts->add(TEXT_EXT_WARN_DELETE_TEMPLATE_SUCCESS, 'success');

            redirect_to('ext/xml_export/templates');
        }
        break;
    case 'get_fields':

        $obj = db_find('app_ext_xml_export_templates', $_POST['id']);

        $html = '
				<div class="form-group">
			  	<label class="col-md-3 control-label" for="users_groups">' . TEXT_START . '</label>
			    <div class="col-md-9">	
			  	  ' . textarea_tag('template_header', $obj['template_header'], ['class' => 'form-control textarea-small code', 'style' => 'font-size:13px;']) . '
			  	  ' . tooltip_text(TEXT_EXT_XML_EXPORT_START_TIP) . '
			    </div>			
			  </div> 
			  
			  <div class="form-group">
			  	<label class="col-md-3 control-label" for="users_groups">' . TEXT_BODY . fields::get_available_fields_helper($_POST['entities_id'], 'template_body') . '</label>
			    <div class="col-md-9">	
			  	  ' . textarea_tag('template_body', $obj['template_body'], ['class' => 'form-control code', 'style' => 'min-height: 260px; font-size:13px;']) . '
			  	  ' . tooltip_text(TEXT_EXT_PREPARE_TEMPLATE_FOR_SINGLE_ITEM . '<br>' . TEXT_ENTER_TEXT_PATTERN_INFO_SHORT) . '
			    </div>			
			  </div>
			  
			  <div class="form-group">
			  	<label class="col-md-3 control-label" for="users_groups">' . TEXT_END . '</label>
			    <div class="col-md-9">	
			  	  ' . textarea_tag('template_footer', $obj['template_footer'], ['class' => 'form-control textarea-small code', 'style' => 'font-size:13px;']) . '      
			    </div>			
			  </div>
			  	  		
			  <p>' . TEXT_EXT_XML_EXPORT_BODY_TIP . '</p>
				';

        echo $html;

        exit();
        break;
    
    case 'get_related_entities':
        $entity_id = $_POST['entities_id'];
        $obj = db_find('app_ext_xml_export_templates', $_POST['id']);
        
        $related_entities = new settings($obj['related_entities_template']);
                        
        $html = '';
        $entities_query = db_query("select * from app_entities where parent_id = '" . $entity_id . "'");
        while($entities = db_fetch_array($entities_query))
        {
            $html .='
                <div class="form-group">
                    <label class="col-md-3 control-label" for="users_groups">' . $entities['name'] . '</label>
                    <div class="col-md-9">	
                          ' . input_tag('entity_block_id','[entity_' . $entities['id'] . ']',['class'=>'form-control input-medium select-all','readonly'=>'readonly']) . '      
                    </div>			
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label" for="users_groups">' . TEXT_BODY . fields::get_available_fields_helper($entities['id'], 'related_entities_entity_' . $entities['id']) . '</label>
                    <div class="col-md-9">	
                          ' . textarea_tag('related_entities[entity_' . $entities['id']. ']', $related_entities->get('entity_' . $entities['id']), ['class' => 'form-control code', 'style' => 'font-size:13px;']) . '      
                    </div>			
                </div>
                <hr>
                ';
        }
        
        $fields_query = db_query("select id, name, configuration, entities_id,type from app_fields where entities_id='" . $entity_id . "' and type in ('fieldtype_entity','fieldtype_entity_ajax','fieldtype_entity_multilevel','fieldtype_related_records','fieldtype_users','fieldtype_users_ajax')");
        while($fields = db_fetch_array($fields_query))
        {
            $cfg = new fields_types_cfg($fields['configuration']);
            
            $entity_id = (in_array($fields['type'],['fieldtype_users','fieldtype_users_ajax']) ? 1 : $cfg->get('entity_id'));
            $entity_name = $app_entities_cache[$entity_id]['name']??'';
            
            $html .='
                <div class="form-group">
                    <label class="col-md-3 control-label" for="users_groups">' . $fields['name'] . ': ' . $entity_name  .  '</label>
                    <div class="col-md-9">	
                          ' . input_tag('entity_block_id','[field_' . $fields['id'] . ']',['class'=>'form-control input-medium select-all','readonly'=>'readonly']) . '      
                    </div>			
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label" for="users_groups">' . TEXT_BODY . fields::get_available_fields_helper($entity_id, 'related_entities_field_' . $fields['id']) . '</label>
                    <div class="col-md-9">	
                          ' . textarea_tag('related_entities[field_' . $fields['id']. ']', $related_entities->get('field_' . $fields['id']), ['class' => 'form-control code', 'style' => 'font-size:13px;']) . '      
                    </div>			
                </div>
                <hr>
                ';
           
        }
        
        echo $html;
        
        exit();
        break;
}