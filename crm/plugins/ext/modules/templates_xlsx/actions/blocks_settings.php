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

$field_query = db_query("select * from app_fields where id=" . _POST('fields_id'));
if(!$field = db_fetch_array($field_query))
{
    exit();
}

if(isset($_POST['id']))
{
    $obj = db_find('app_ext_items_export_templates_blocks',$_POST['id']);
}
else
{
    $obj = db_show_columns('app_ext_items_export_templates_blocks');
}

//for subentities
$is_subentity = false;
if($field['type']=='fieldtype_id' and $app_entities_cache[$field['entities_id']]['parent_id']==$template_info['entities_id'])
{
    $field['type'] = 'fieldtype_entity';
    $field_entity_id = $field['entities_id'];    
    $is_subentity = true;
}



$cfg = new settings($field['configuration']);

$settings = new settings($obj['settings']);

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
        
        $choices = [];
        $choices[''] = '';
        
        foreach($app_num2str->data as $k=>$v)
        {
            $choices[$k] = $k;
        }
        
        $html ='
          <div class="form-group">
            <label class="col-md-3 control-label" for="fields_id">' . tooltip_icon(TEXT_NUMBER_FORMAT_INFO) . TEXT_NUMBER_FORMAT . '</label>
            <div class="col-md-9">' .  input_tag('settings[number_format]',$settings->get('number_format',CFG_APP_NUMBER_FORMAT),['class'=>'form-control input-small input-masked','data-mask'=>'9/~/~']) . '</div>			
          </div>  
          <div class="form-group">
            <label class="col-md-3 control-label" for="fields_id">' . TEXT_PREFIX . '</label>
            <div class="col-md-9">' .  input_tag('settings[content_value_prefix]',$settings->get('content_value_prefix',''),['class'=>'form-control input-medium']) . '</div>			
          </div>

          <div class="form-group">
            <label class="col-md-3 control-label" for="fields_id">' .  TEXT_SUFFIX . '</label>
            <div class="col-md-9">' .  input_tag('settings[content_value_suffix]',$settings->get('content_value_suffix',''),['class'=>'form-control input-medium']) . '</div>			
          </div>
          
          <div class="form-group">
            <label class="col-md-3 control-label" for="fields_id">' . tooltip_icon(TEXT_EXT_NUMBER_IN_WORDS_INFO) . TEXT_EXT_NUMBER_IN_WORDS  . '</label>
            <div class="col-md-9">' . select_tag('settings[number_in_words]',$choices,$settings->get('number_in_words'),['class'=>'form-control input-small']) . '</div>
          </div>';
        break;
               
    case 'fieldtype_entity':
    case 'fieldtype_entity_ajax':
    case 'fieldtype_related_records':
    case 'fieldtype_users':
    case 'fieldtype_users_ajax':
    case 'fieldtype_users_approve':
    case 'fieldtype_user_roles':    
                
        if(!in_array($cfg->get('display_as'),['dropdown_multiple','checkboxes','dropdown_muliple']) and !isset($field_entity_id) and $field['type']!='fieldtype_related_records') break;
               
        
        if(!isset($field_entity_id))
        {
            $field_entity_id = (in_array($field['type'],['fieldtype_users','fieldtype_users_ajax','fieldtype_users_approve']) ? 1 : $cfg->get('entity_id'));
        }
        
        $list_dislay_choices = [
            'inline' => TEXT_IN_ONE_LINE,            
            'table' => TEXT_TABLE,            
        ];  
        
        if($is_subentity)
        {
            //filter by reports ID
            $html .= '
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_REPORT . '</label>
                <div class="col-md-9">' . input_tag('settings[reports_id]',$settings->get('reports_id'),['class'=>'form-control input-small number'])  . tooltip_text(TEXT_EXT_ENTER_REPORT_ID_TO_FILTER). '</div>
            </div>';
        }
        
        $html .='
          <div class="form-group">
            <label class="col-md-3 control-label" for="fields_id">' . TEXT_DISPLAY_AS . '</label>
            <div class="col-md-9">' . select_tag('settings[display_us]',$list_dislay_choices,$settings->get('display_us'),['class'=>'form-control input-medium']) . '</div>			
          </div>  
          
          <div class="form-group settings-list settings-inline">
            <label class="col-md-3 control-label" for="fields_id">' . TEXT_PATTERN  . fields::get_available_fields_helper($field_entity_id, 'settings_pattern') . '</label>
            <div class="col-md-9">' . input_tag('settings[pattern]',$settings->get('pattern'),['class'=>'form-control input-xlarge code']) . tooltip_text(TEXT_HEADING_TEMPLATE_INFO).  '</div>			
          </div>
          <div class="form-group settings-inline">
            <label class="col-md-3 control-label" for="fields_id">' . TEXT_SEPARATOR . '</label>
            <div class="col-md-9">' . input_tag('settings[separator]',$settings->get('separator',', '),['class'=>'form-control input-small']) . '</div>			
          </div>
                ';
        
        break;
    case 'fieldtype_access_group':
    case 'fieldtype_tags':
    case 'fieldtype_grouped_users':
    case 'fieldtype_checkboxes':
    case 'fieldtype_dropdown_multiple':
        
       
        $html ='
          <div class="form-group">
            <label class="col-md-3 control-label" for="fields_id">' . TEXT_DISPLAY_AS . '</label>
            <div class="col-md-9"><p class="form-control-static">' . TEXT_IN_ONE_LINE . '</p></div>
          </div>
                                       
          <div class="form-group settings-inline">
            <label class="col-md-3 control-label" for="fields_id">' . TEXT_SEPARATOR . '</label>
            <div class="col-md-9">' . input_tag('settings[separator]',$settings->get('separator',', '),['class'=>'form-control input-small']) . '</div>
          </div>
                ';
        
        break;                
    
}

echo $html;

?>

<script>
$(function(){
		
        $('#settings_display_us').change(function(){
                show_box_settigns();
        })
	
        show_box_settigns();
})

function show_box_settigns()
{
        $('.settings-list, .settings-inline, .settings-table').hide();
	
        switch($('#settings_display_us').val())
        {
                case 'inline': $('.settings-inline').show();
                        break; 
                case 'list': $('.settings-list').show();
                        break;
                case 'table': $('.settings-table').show();
                        break;
                case 'table_list': $('.settings-table').show();
                        break;
                case 'tree_table': $('.settings-table').show();
                        break;
        }

        $(window).resize();
}
</script>



