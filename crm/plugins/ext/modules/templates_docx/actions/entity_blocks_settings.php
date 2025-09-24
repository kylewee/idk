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


$cfg = new settings($field['configuration']);

$settings = new settings($obj['settings']);

$html = '';


switch($field['type'])
{
    case 'fieldtype_input_date':
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
    case 'fieldtype_attachments':
    case 'fieldtype_image':
    case 'fieldtype_image_ajax':
    case 'fieldtype_user_photo':
        $html = '
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_WIDHT . '</label>
                <div class="col-md-9">' . input_tag('settings[width]',$settings->get('width'),['class'=>'form-control input-small number']) . '</div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_HEIGHT . '</label>
                    <div class="col-md-9">' . input_tag('settings[height]',$settings->get('height'),['class'=>'form-control input-small number']) . '</div>
            </div>
         ';
        
        if($field['type']=='fieldtype_attachments')
        {
            $html .= '
                <div class="form-group">
                    <label class="col-md-3 control-label" for="fields_id">' . TEXT_GRID . '</label>
                    <div class="col-md-9">' . input_tag('settings[grid]',$settings->get('grid',1),['class'=>'form-control input-xsmall number']) . '</div>
                </div>';
        }
        
        break;
}

echo $html;