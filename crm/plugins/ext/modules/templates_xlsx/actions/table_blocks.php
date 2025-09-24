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
        $sql_data = [
        'templates_id' => $template_info['id'],
        'block_type' => 'body_cell',
        'parent_id' => $parent_block['id'],
        'fields_id' => _POST('fields_id'),
        'settings' => (isset($_POST['settings']) ? json_encode($_POST['settings']) : ''),
        'sort_order' => $_POST['sort_order'],
        ];
        
        if(isset($_GET['id']))
        {
            db_perform('app_ext_items_export_templates_blocks',$sql_data,'update',"id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_ext_items_export_templates_blocks',$sql_data);
        }
        
        redirect_to('ext/templates_xlsx/table_blocks','templates_id=' . $template_info['id'] . '&parent_block_id=' . $parent_block['id']);
        break;
    case 'delete':
        if(isset($_GET['id']))
        {
            export_templates_blocks::delele_block(_GET('id'));
            
            redirect_to('ext/templates_xlsx/table_blocks','templates_id=' . $template_info['id'] . '&parent_block_id=' . $parent_block['id']);
        }
        break;
    case 'get_field_settings':
        $field_query = db_query("select type from app_fields where id=" . _POST('fields_id'));
        if(!$field = db_fetch_array($field_query))
        {
            exit();
        }
        
        if($_GET['id']>0)
        {      
            $obj = db_find('app_ext_items_export_templates_blocks',_GET('id'));
            $settings = new settings($obj['settings']);
        }
        else
        {
            $settings = new settings('');
        }
        
        $html = '';
        
        switch($field['type'])
        {
            case 'fieldtype_input_date':
            case 'fieldtype_input_date_extra':    
                $html = '
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="fields_id">' . TEXT_DATE_FORMAT . '</label>
                        <div class="col-md-9">' . input_tag('settings[date_format]',$settings->get('date_format'),['class'=>'form-control input-small'])  . tooltip_text(TEXT_DEFAULT .': ' . CFG_APP_DATE_FORMAT . ', ' . TEXT_DATE_FORMAT_IFNO). '</div>
                    </div>';
                
                break;
            case 'fieldtype_date_added':
            case 'fieldtype_date_updated':
            case 'fieldtype_dynamic_date':
            case 'fieldtype_input_datetime':
                $html = '
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="fields_id">' . TEXT_DATE_FORMAT . '</label>
                        <div class="col-md-9">' . input_tag('settings[date_format]',$settings->get('date_format'),['class'=>'form-control input-small'])  . tooltip_text(TEXT_DEFAULT .': ' . CFG_APP_DATETIME_FORMAT . ', ' . TEXT_DATE_FORMAT_IFNO). '</div>
                    </div>';
                
                break;
            
            case 'fieldtype_input_numeric':
            case 'fieldtype_input_numeric_comments':
            case 'fieldtype_formula':
            case 'fieldtype_js_formula':
            case 'fieldtype_mysql_query':
            case 'fieldtype_ajax_request':
                $html = '
                  <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id">' . tooltip_icon(TEXT_NUMBER_FORMAT_INFO) . TEXT_NUMBER_FORMAT . '</label>
                    <div class="col-md-9">' .  input_tag('settings[number_format]',$settings->get('number_format',CFG_APP_NUMBER_FORMAT),['class'=>'form-control input-small input-masked','data-mask'=>'9/~/~']) . '</div>			
                  </div>  
                  <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id">' . TEXT_PREFIX . '</label>
                    <div class="col-md-9">' .  input_tag('settings[content_value_prefix]',$settings->get('content_value_prefix',''),['class'=>'form-control input-medium']) . '</div>			
                  </div>
                  
                  <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id">' .  TEXT_SUFFIX . '</label>
                    <div class="col-md-9">' .  input_tag('settings[content_value_suffix]',$settings->get('content_value_suffix',''),['class'=>'form-control input-medium']) . '</div>			
                  </div>
                  ';
                break;
        }
        
        echo $html;
        
        exit();
        
        break;
}