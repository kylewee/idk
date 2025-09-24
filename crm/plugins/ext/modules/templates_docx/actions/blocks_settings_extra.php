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

if(isset($_POST['id']))
{
    $obj = db_find('app_ext_items_export_templates_blocks',$_POST['id']);
}
else
{
    $obj = db_show_columns('app_ext_items_export_templates_blocks');
}

$settings = new settings($obj['settings']);

$extra_type = $_POST['extra_type'];

$filters = [];
$filters[] = '<code>[current_user_id]</code>';
$filters[] = '<code>[current_item_id]</code>';
$filters_tip = count($filters) ? TEXT_EXAMPLE . ' 3: ' . implode(', ', $filters):'';

$html = '';

switch($extra_type)
{    
    case 'php':
        $html .= '
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_PHP_CODE . '</label>
                <div class="col-md-9">' . textarea_tag('settings[php_code]',$settings->get('php_code'),['class'=>'code_mirror','mode'=>'php']) . tooltip_text(TEXT_EXAMPLE . ' 1: <code>$output_value = "my value' . htmlspecialchars('<br>'). 'next line";</code><br>' . TEXT_EXAMPLE . ' 2: <code>$output_value = $item["field_13"]>0 ? 1:0;</code><br>' . $filters_tip) . '</div>			
            </div>
            
            <div class="form-group settings-list settings-table">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_EXT_FONT_NAME . '</label>
                <div class="col-md-4">' . input_tag('settings[font_name]',$settings->get('font_name','Times New Roman'),['class'=>'form-control input-medium required']) . tooltip_text(TEXT_EXAMPLE . ': Times New Roman, Arial') . '</div>
                <div class="col-md-3 settings-table">' . input_color('settings[font_color]',$settings->get('font_color')) . '</div>			
              </div>

              <div class="form-group settings-list settings-table">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_EXT_FONT_SIZE . '</label>
                <div class="col-md-9">' . input_tag('settings[font_size]',$settings->get('font_size','12'),['class'=>'form-control input-small required number']) . '</div>			
              </div>
            ';
        break;        
    case 'table':
         
        $direction_choices = [
            '' => '<i class="fa fa-long-arrow-right" aria-hidden="true"></i>',
            'BTLR' => '<i class="fa fa-long-arrow-up" aria-hidden="true"></i>',
            'TBRL' => '<i class="fa fa-long-arrow-down" aria-hidden="true"></i>',
        ];
        
        $html .= '
            
        <ul class="nav nav-tabs">
            <li class="active"><a href="#general_info"  data-toggle="tab">' . TEXT_MYSQL_QUERY . '</a></li>            
            <li><a href="#table_settings"  data-toggle="tab">' . TEXT_SETTINGS . '</a></li>     
        </ul> 
        
        <div class="tab-content">
          <div class="tab-pane fade active in" id="general_info">
        
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_MYSQL_QUERY . '</label>
                <div class="col-md-9">' . textarea_tag('settings[mysql_query]',$settings->get('mysql_query'),['class'=>'code_mirror','mode'=>'sql']) . tooltip_text($filters_tip) . '</div>			
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_DEBUG_MODE . '</label>
                <div class="col-md-9">' . select_tag_toogle('settings[debug_mode]',$settings->get('debug_mode')) . '</div>			
            </div>                                                
            
          </div>
          
          <div class="tab-pane fade" id="table_settings">
          
             <div class="form-group settings-list settings-table">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_HIDE_IF_EMPTY . '</label>
                <div class="col-md-9">' . select_tag('settings[hide_if_empty]',['0'=>TEXT_NO, '1'=>TEXT_YES],$settings->get('hide_if_empty'),['class'=>'form-control input-small']) . '</div>			
              </div>
              
              <div class="form-group settings-list settings-table">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_EXT_FONT_NAME . '</label>
                <div class="col-md-4">' . input_tag('settings[font_name]',$settings->get('font_name','Times New Roman'),['class'=>'form-control input-medium required']) . tooltip_text(TEXT_EXAMPLE . ': Times New Roman, Arial') . '</div>
                <div class="col-md-3 settings-table">' . input_color('settings[font_color]',$settings->get('font_color')) . '</div>			
              </div>

              <div class="form-group settings-list settings-table">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_EXT_FONT_SIZE . '</label>
                <div class="col-md-9">' . input_tag('settings[font_size]',$settings->get('font_size','12'),['class'=>'form-control input-small required number']) . '</div>			
              </div>
              
            <!--table-->               
              <div class="form-group settings-table">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_EXT_BORDER . '</label>
                <div class="col-md-1">' . input_tag('settings[border]',$settings->get('border','0.1'),['class'=>'form-control input-xsmall required number']) . '</div>
                <div class="col-md-3">' . input_color('settings[border_color]',$settings->get('border_color')) . '</div> 
              </div>
              <div class="form-group settings-table">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_BACKGROUND_COLOR . '</label>           
                <div class="col-md-3">' . input_color('settings[table_color]',$settings->get('table_color')) . '</div> 
              </div>
              <div class="form-group settings-table">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_EXT_CELL_MARGIN . '</label>
                <div class="col-md-9">' . input_tag('settings[cell_margin]',$settings->get('cell_margin','3'),['class'=>'form-control input-xsmall required number']) . '</div>			
              </div>
              <div class="form-group settings-table">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_EXT_CELL_SPACING . '</label>
                <div class="col-md-9">' . input_tag('settings[cell_spacing]',$settings->get('cell_spacing','0'),['class'=>'form-control input-xsmall required number']) . '</div>			
              </div>
              <div class="form-group settings-table">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_EXT_HEADER_HEIGHT . '</label>
                <div class="col-md-1">' . input_tag('settings[header_height]',$settings->get('header_height',''),['class'=>'form-control input-xsmall number']) . '</div>
                <div class="col-md-3">' . input_color('settings[header_color]',$settings->get('header_color')) . '</div>			
              </div>
              <div class="form-group settings-table">
                <label class="col-md-3 control-label" for="settings_line_numbering">' . TEXT_EXT_LINE_NUMBERING . '</label>
                <div class="col-md-1"><p class="form-control-static">' . input_checkbox_tag('settings[line_numbering]',1,['checked'=>$settings->get('line_numbering')]) . '</p></div>
                <div class="col-md-2">' . input_tag('settings[line_numbering_heading]',$settings->get('line_numbering_heading'),['class'=>'form-control input-small','placeholder'=>TEXT_HEADING]) . '</div>
                <div class="col-md-3">' . select_radioboxes_button('settings[line_numbering_direction]',$direction_choices,$settings->get('line_numbering_direction','')) . '</div>			
              </div>
              <div class="form-group settings-table">
                <label class="col-md-3 control-label" for="settings_column_numbering">' . TEXT_EXT_COLUMN_NUMBERING . '</label>
                <div class="col-md-1"><p class="form-control-static">' . input_checkbox_tag('settings[column_numbering]',1,['checked'=>$settings->get('column_numbering')]) . '</p></div>            		
              </div> 
          </div>
                                        
        </div>
            ';
        
        break;
}

echo $html;

exit();

