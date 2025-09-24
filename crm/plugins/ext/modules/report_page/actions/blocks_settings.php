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


$report_page_query = db_query("select * from app_ext_report_page where id='" . _GET('report_id') . "'");
if(!$report_page = db_fetch_array($report_page_query))
{    
    exit();
}

if(isset($_POST['id']))
{
    $obj = db_find('app_ext_report_page_blocks',$_POST['id']);
}
else
{
    $obj = db_show_columns('app_ext_report_page_blocks');
}

$block_type = $_POST['block_type']??'';
$settings = new settings($obj['settings']);
$report_settings = new settings($report_page['settings']);


$filters = [];

$filters[] = '<code>[current_user_id]</code>';
        
if($report_settings->get('filter_by_date')==1)
{
    if($report_settings->get('filter_by_date_mode')=='date_range')
    {
        $filters[] = '<code>[filter_by_date_from]</code>';
        $filters[] = '<code>[filter_by_date_to]</code>';
    }
    else
    {
        $filters[] = '<code>[filter_by_date]</code>';
    }
}

if($report_settings->get('filter_by_user')==1)
{
    $filters[] = '<code>[filter_by_user]</code>';
}

if($report_page['entities_id']>0)
{
    $filters[] = '<code>[current_item_id]</code>';
}

for($i=1;$i<=report_page\report_filters::COUNT_EXTRA_FILTERS;$i++)
{
    if($report_settings->get('filter_by_entity' . $i)==1)
    {
        $filters[] = '<code>[filter_by_entity' . $i .']</code>';
    }
}

for($i=1;$i<=report_page\report_filters::COUNT_EXTRA_FILTERS;$i++)
{
    if($report_settings->get('filter_by_list' . $i)==1)
    {
        $filters[] = '<code>[filter_by_list' . $i .']</code>';
    }
}
        

$filters_tip = count($filters) ? TEXT_FILTERS . ': ' . implode(', ', $filters):'';

$html = '';

switch($block_type)
{
    case 'field':
        $choices = fields::get_choices($report_page['entities_id'],['include_parents'=>true]);
        $html .= '
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_FIELD . '</label>
                <div class="col-md-9">' . select_tag('field_id',$choices, $obj['field_id'],['class'=>'input-xlarge chosen-select']) . '</div>			
            </div>
            
            <div id="field_settings"></div>
            
            <script>
            field_settings();

            $("#field_id").change(function()
            {
                field_settings();
            })
            </script>
            ';
        break;
    case 'nested_entity':
        $html .= '
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_ENTITY . '</label>
                <div class="col-md-9">' . select_tag('settings[entity_id]',entities::get_nested_choices($report_page['entities_id']), $settings->get('entity_id'),['class'=>'input-xlarge chosen-select']) . '</div>			
            </div>
            
            <div id="nested_entity_settings"></div>
            
            <script>
            nested_entity_settings();

            $("#settings_entity_id").change(function()
            {
                nested_entity_settings();
            })
            </script>
            ';
        break;
    case 'php':
        $html .= '
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_PHP_CODE . '</label>
                <div class="col-md-9">' . textarea_tag('settings[php_code]',$settings->get('php_code'),['class'=>'code_mirror','mode'=>'php']) . tooltip_text(TEXT_EXAMPLE . ': <code>$output_value = "my value";</code><br>' . $filters_tip) . '</div>			
            </div>
            ';
        break;
    case 'html':
        $html .= '
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_HTML_CODE . '</label>
                <div class="col-md-9">' . textarea_tag('settings[html_code]',$settings->get('html_code'),['class'=>'code_mirror','mode'=>'xml']) . '</div>			
            </div>
            ';
        break;
    
    case 'table':
                
        $html .= '
            
        <ul class="nav nav-tabs">
            <li class="active"><a href="#general_info"  data-toggle="tab">' . TEXT_SETTINGS . '</a></li>
            <li><a href="#pagination"  data-toggle="tab">' . TEXT_PAGINATION . '</a></li>
            <li><a href="#chart_settings"  data-toggle="tab">' .  TEXT_EXT_GRAPHIC_REPORT . '</a></li>                           
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
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . tooltip_icon(TEXT_EXT_EXPORT_TABLE_BUTTON_TIP) . TEXT_EXPORT . ' (XLS)</label>
                <div class="col-md-9">' . select_tag_toogle('settings[xls_export]',$settings->get('xls_export')) . '</div>			
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . sprintf(TEXT_EXT_TAG_X_ATTRIBUTES,'TABLE')  . '</label>
                <div class="col-md-9">' . input_tag('settings[tag_table_attributes]',$settings->get('tag_table_attributes'),['class'=>'form-control input-xlarge code']) . tooltip_text(TEXT_DEFAULT . ': <code>class="table table-striped table-bordered table-hover"</code>').  '</div>			
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . tooltip_icon(TEXT_EXT_MAXIMUM_TABLE_HEIGHT) . TEXT_HEIGHT . '</label>
                <div class="col-md-9">' . input_tag('settings[table_height]',$settings->get('table_height'),['class'=>'form-control input-small','type'=>'number']) . '</div>			
            </div>
            <div class="form-group settings-table">
                <label class="col-md-3 control-label" for="settings_line_numbering">' . TEXT_EXT_LINE_NUMBERING . '</label>
                <div class="col-md-1"><p class="form-control-static">' . input_checkbox_tag('settings[line_numbering]',1,['checked'=>$settings->get('line_numbering')]) . '</p></div>                
                <div class="col-md-2">' . input_tag('settings[line_numbering_heading]',$settings->get('line_numbering_heading'),['class'=>'form-control input-small','placeholder'=>TEXT_HEADING]) . '</div>
            </div>
            <div class="form-group settings-table">
                <label class="col-md-3 control-label" for="settings_column_numbering">' . TEXT_EXT_COLUMN_NUMBERING . '</label>
                <div class="col-md-1"><p class="form-control-static">' . input_checkbox_tag('settings[column_numbering]',1,['checked'=>$settings->get('column_numbering')]) . '</p></div>            		
            </div> 
            
          </div>
          
          <div class="tab-pane fade" id="pagination">
          
                <div class="form-group">
                    <label class="col-md-3 control-label" for="fields_id">' . TEXT_PAGINATION . '</label>
                    <div class="col-md-9">' . select_tag_toogle('settings[pagination]',$settings->get('pagination')) . '</div>			
                </div>
                <div form_display_rules="settings_pagination:1">
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="fields_id">' .  TEXT_ROWS_PER_PAGE . '</label>
                        <div class="col-md-9">' . input_tag('settings[rows_per_page]',$settings->get('rows_per_page',10),['class'=>'form-control input-small','type'=>'number']) . '</div>			
                    </div>
                    
                    <div class="form-group settings-table">
                        <label class="col-md-3 control-label" for="settings_sort_values">' . TEXT_SORT_VALUES . '</label>
                        <div class="col-md-1"><p class="form-control-static">' . input_checkbox_tag('settings[sort_values]',1,['checked'=>$settings->get('sort_values')]) . '</p></div>            		
                    </div>

                    <div class="form-group settings-table">
                        <label class="col-md-3 control-label" for="settings_allow_search">' . TEXT_USE_SEARCH . '</label>
                        <div class="col-md-1"><p class="form-control-static">' . input_checkbox_tag('settings[allow_search]',1,['checked'=>$settings->get('allow_search')]) . '</p></div>            		
                    </div>
                </div>
                
          </div>
          
          <div class="tab-pane fade" id="chart_settings">';
        

    $choices_chart_type = [
        ''=>'',
        'pie'=>TEXT_EXT_PIE_CHART,        
        'column'=>TEXT_EXT_COLUMN_CHART,        
        'stacked_column' => TEXT_EXT_STACKED_COLUMN_CHART,
        'stacked_percent' => TEXT_EXT_STACKED_PERCENT_COLUMN_CHART,
        'bar'=>TEXT_EXT_BAR_CHART,
        'line'=>TEXT_EXT_LINE_CHART,
        'spline'=>TEXT_EXT_SPLINE_CHART,
        'funnel'=>TEXT_EXT_FUNNEL_CHART,        
        'pyramid'=>TEXT_EXT_PYRAMID_CHART,         
        'area' => TEXT_EXT_AREA_CHART,
        'stacked_area' => TEXT_EXT_STACKED_AREA_CHART,
        
    ];    

        $html .= '
        <div class="form-group">
            <label class="col-md-4 control-label" for="type">' . TEXT_TYPE . '</label>
            <div class="col-md-8">	
                ' . select_tag('settings[chart_type]',$choices_chart_type, $settings->get('chart_type'),array('class'=>'form-control input-large')) . '
            </div>			
        </div>
        
        <div class="form-group" form_display_rules="settings_chart_type:column,line,spline">
            <label class="col-md-4 control-label" for="settings_chart_show_totals">' . TEXT_EXT_SHOW_TOTALS_IN_CHART . '</label>
            <div class="col-md-8 form-control-static">	
                ' . input_checkbox_tag('settings[chart_show_totals]',1,['checked'=>$settings->get('chart_show_totals')]) . '       
            </div>			
        </div>
        ';
                

    $choices = [
        'right' => TEXT_ON_RIGHT,
        'left' => TEXT_ON_LEFT,        
        'top' => TEXT_ON_TOP,
        'bottom' => TEXT_ON_BOTTOM,
        'only_chart' => TEXT_EXT_ONLY_CHART,
    ];

        $html .= '    
        <div class="form-group">
            <label class="col-md-4 control-label" for="position">' . TEXT_POSITION . '</label>
            <div class="col-md-8">	
                ' . select_tag('settings[chart_position]',$choices, $settings->get('chart_position'),array('class'=>'form-control input-medium')) . '       
            </div>			
        </div>';
        
        
        $choices = [];
        if($obj['id']>0)
        {            
            $blocks_query = db_query("select b.*,f.type as field_type, f.name as field_name, f.entities_id as field_entity_id from app_ext_report_page_blocks b left join app_fields f on b.field_id=f.id  where block_type='body_cell' and b.report_id={$report_page['id']} and b.parent_id = " . $obj['id'] . " order by b.sort_order, b.id");
            while($blocks = db_fetch_array($blocks_query))
            {
                $s = new settings($blocks['settings']);
                $choices[$blocks['id']] = $s->get('heading') . ' [#' . $blocks['id']. ']';
            }
        }
        
        $html .= ' 
        <div class="form-group">
            <label class="col-md-4 control-label">' . TEXT_EXT_HORIZONTAL_AXIS . '</label>
            <div class="col-md-8">	
          	' . select_tag('settings[chart_xaxis]', $choices, $settings->get('chart_xaxis'), array('class' => 'form-control input-large')) . '               
            </div>			
        </div>';
        
        
        $chosen_order = '';
        $order_sql = 'order by b.sort_order, b.id';
        if(is_array($settings->get('chart_yaxis')))
        {
            $chosen_order = implode(',',$settings->get('chart_yaxis'));
            $order_sql = "order by field(b.id,{$chosen_order})";
        }
        
        $choices = [];
        if($obj['id']>0)
        {            
            $blocks_query = db_query("select b.*,f.type as field_type, f.name as field_name, f.entities_id as field_entity_id from app_ext_report_page_blocks b left join app_fields f on b.field_id=f.id  where block_type='body_cell' and b.report_id={$report_page['id']} and b.parent_id = " . $obj['id'] . " order by b.sort_order, b.id");
            while($blocks = db_fetch_array($blocks_query))
            {
                $s = new settings($blocks['settings']);
                $choices[$blocks['id']] = $s->get('heading') . ' [#' . $blocks['id']. ']';
            }
        }
        
        $html .= ' 
        <div class="form-group">
            <label class="col-md-4 control-label">' . TEXT_EXT_VERTICAL_AXIS . '</label>
            <div class="col-md-8">	
          	' . select_tag('settings[chart_yaxis][]', $choices, $settings->get('chart_yaxis'), array('class' => 'form-control input-xlarge chosen-select chosen-sortable','multiple'=>'multiple','chosen_order'=>$chosen_order)) . '               
            </div>			
        </div>
        
          
      
        <div class="form-group">
            <label class="col-md-4 control-label" for="chart_height">' . tooltip_icon(TEXT_DEFAULT .': 600') . TEXT_HEIGHT . '</label>
          <div class="col-md-8">	
              ' . input_tag('settings[chart_height]',$settings->get('chart_height'),array('class'=>'form-control input-small','type'=>'number')) . '        
          </div>			
        </div>';  
        
        
        $html .= '
        <p class="form-section">' . TEXT_VALUE . '</p>

        <div class="form-group">
            <label class="col-md-4 control-label" for="cfg_numer_format">' . tooltip_icon(TEXT_EXT_PIVOTREPORTS_NUMBER_FORMAT_INFO) . TEXT_NUMBER_FORMAT . '</label>
            <div class="col-md-8">	
                ' . input_tag('settings[chart_number_format]', $settings->get('chart_number_format'), array('class' => 'form-control input-small input-masked', 'data-mask' => '9/~/~')) . '	      
            </div>			
        </div>
       
        <div class="form-group">
            <label class="col-md-4 control-label" for="cfg_numer_format">' . TEXT_PREFIX . '</label>
            <div class="col-md-8">	
                ' . input_tag('settings[chart_number_prefix]', $settings->get('chart_number_prefix'), array('class' => 'form-control input-small ')) . '	      
            </div>			
        </div>

        <div class="form-group">
            <label class="col-md-4 control-label" for="cfg_numer_format">' . TEXT_SUFFIX . '</label>
            <div class="col-md-8">	
                ' . input_tag('settings[chart_number_suffix]', $settings->get('chart_number_suffix'), array('class' => 'form-control input-small ')) . '	      
            </div>			
        </div>';
        
        

$colors = is_array($settings->get('chart_colors')) ? $settings->get('chart_colors') : [];
$types = is_array($settings->get('chart_types')) ? $settings->get('chart_types') : [];
$color_html = '';
for($i = 0; $i<10; $i++)
{
   $color_html .= '<table style="margin-bottom: 5px;"><tr>';
   $color_html .= '<td>' . input_color('settings[chart_colors][' . $i . ']',(isset($colors[$i]) ? $colors[$i]:'')) . '</td>'; 
   $color_html .= '<td style="padding-left: 10px;">' . select_tag('settings[chart_types][' . $i . ']',$choices_chart_type, (isset($types[$i]) ? $types[$i]:''),array('class'=>'form-control input-large')) . '</td>';
   $color_html .= '</tr></table>';
}

        
        $html .= '
            <div class="form-group">
                <label class="col-md-4 control-label">' . tooltip_icon(TEXT_EXT_PIVOT_TABLES_COLORS_TIP) . TEXT_COLOR . '</label>
                <div class="col-md-8">	
                    ' . $color_html . '
                </div>			
            </div>
            ';
        
        $html .= '
          </div>
        </div>
            ';
        
        break;
}

echo $html;

exit();
