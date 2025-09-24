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

class pivot_tables
{
    private $chart_settigns;
    
    public $version, $pivot_table, $id, $entities_id;
    
    function __construct($pivot_table)
    {
        $this->version = '1.3.3';
        
        $this->pivot_table = $pivot_table;
        $this->id = $this->pivot_table['id'];
        $this->entities_id = $this->pivot_table['entities_id'];
        
        $this->set_chart_settings();                
    }
    
    function set_chart_settings()
    {
        
        $number_format = strlen($this->pivot_table['chart_number_format']) ? explode('/',$this->pivot_table['chart_number_format']) : [];
        
        $this->chart_settigns = [
            'decimals' => $number_format[0]??2,
            'decimal_separator' => $number_format[1]??'.',
            'thousands_separator' => $number_format[2]??' ',
            'show_labels' => $this->pivot_table['chart_show_labels'],
            'prefix' => $this->pivot_table['chart_number_prefix'],
            'suffix' => $this->pivot_table['chart_number_suffix'],
        ];
    }
    
    function render_layout()
    {
        $html = '';
        if(strlen($this->pivot_table['chart_type']))
        {
            switch($this->pivot_table['chart_position'])
            {
                case 'right':
                    $html = '
                        <div class="row">
                            <div class="col-md-6">
                                <div id="pivot_table_' . $this->id . '"></div>
                            </div>        
                            <div class="col-md-6">
                                <div id="pivot_table_' . $this->id . '_chart" class="pivot_table_chart_side" style="height: ' . $this->get_chart_height() . 'px;"></div>
                            </div>        
                        </div>';
                    break;
                case 'left':
                    $html = '
                        <div class="row">
                            <div class="col-md-6">
                                <div id="pivot_table_' . $this->id . '_chart" class="pivot_table_chart_side" style="height: ' . $this->get_chart_height() . 'px;"></div>
                            </div>        
                            <div class="col-md-6">
                                <div id="pivot_table_' . $this->id . '"></div>
                            </div>                                    
                        </div>';
                    break;
                case 'top':
                    $html = ''
                        . '<div id="pivot_table_' . $this->id . '_chart" style="height: ' . $this->get_chart_height() . 'px;"></div>'
                        . '<div id="pivot_table_' . $this->id . '"></div>';
                    break;
                case 'bottom':
                    $html = ''
                        . '<div id="pivot_table_' . $this->id . '"></div>'
                        . '<div id="pivot_table_' . $this->id . '_chart" style="height: ' . $this->get_chart_height() . 'px;"></div>';
                    break;
                case 'only_chart':
                    $html = ''
                        . '<div class="pivot_table_bar" id="pivot_table_bar_' . $this->id . '">
                             <div class="pivot_table_bar_action"></div>
                             <div id="pivot_table_' . $this->id . '"></div>
                           </div>'
                        . '<div id="pivot_table_' . $this->id . '_chart" style="height: ' . $this->get_chart_height() . 'px;"></div>';
                    break;
            }
        }
        else
        {
           $html = '<div id="pivot_table_' . $this->id . '"></div>'; 
        }
                
        return $html . '<p></p>';
    }
    
    function render_chart()
    {        
        if(!strlen($this->pivot_table['chart_type'])) return '';
        
        $chart_type = $this->pivot_table['chart_type'];
        
        $html = '';
        
        if(strlen($colors = $this->get_colors()))
        {
            $html .="
                Highcharts.theme = {
                    colors: [" . $colors . "]
                }

                Highcharts.setOptions(Highcharts.theme);    
                ";
        }
        
        
        
        if($this->chart_settigns['show_labels'])
        {
            $html .=" 
                    Highcharts.setOptions({
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                formatter: function () {
                                    return '" . $this->chart_settigns['prefix'] . "'+Highcharts.numberFormat(this.y," . $this->chart_settigns['decimals'] . ",'" . $this->chart_settigns['decimal_separator'] . "','" . $this->chart_settigns['thousands_separator'] . "')+'" . $this->chart_settigns['suffix'] . "';
                                }
                            }
                        }
                     }
                    });    
                ";
        }
       
        
        $html .=" 
                Highcharts.setOptions({ 
                    chart:{
                        styledMode: true
                    },
                    tooltip: {
                        formatter: function () {
                            return '" . $this->chart_settigns['prefix'] . "'+Highcharts.numberFormat(this.y," . $this->chart_settigns['decimals'] . ",'" . $this->chart_settigns['decimal_separator'] . "','" . $this->chart_settigns['thousands_separator'] . "')+'" . $this->chart_settigns['suffix'] . "';
                        }
                    }                                             
                });    
                ";
        
                                                  
        
        switch($chart_type)
        {
            case 'stacked_column':
                $chart_type = 'column';
                
                $html .=" 
                    Highcharts.setOptions({
                        plotOptions: {
                            column: {
                              stacking: 'normal'
                            }
                          }
                    });    
                ";
                break;
            case 'stacked_percent':
                $chart_type = 'column';
                
                $html .=" 
                    Highcharts.setOptions({
                        plotOptions: {
                            column: {
                              stacking: 'percent'
                            }
                          }
                    });    
                ";
                break;
            case 'stacked_area':
                $chart_type = 'area';
                
                $html .= "
                    Highcharts.setOptions({
                        plotOptions: {
                            area: {
                                stacking: 'normal',
                                lineColor: '#666666',
                                lineWidth: 1,
                                marker: {
                                    lineWidth: 1,
                                    lineColor: '#666666'
                                }
                            }
                        }
                    }); 
                    ";
                break;            
        }
        
        $series_html = '';
        if(strlen($this->pivot_table['chart_types']))
        {
            foreach(explode(',', $this->pivot_table['chart_types']) as $k=>$v)
            {
                if(strlen($v))
                {
                    $series_html .= '
                        data.series[' . $k . '].type = "' . $v . '";';
                }
            }
        }
        
        
        $html .= '            
            pivot_table' . $this->id . '.on("reportcomplete", function() {
                pivot_table' . $this->id . '.off("reportcomplete");
                    
                pivot_table' . $this->id . '.highcharts.getData({
                    type: "' . $chart_type . '",                    
                }, function(data) {                           
                    ' . $series_html . '                    
                    Highcharts.chart("pivot_table_' . $this->id . '_chart", data);
                }, function(data) {                      
                    Highcharts.chart("pivot_table_' . $this->id . '_chart", data);
                });                  

            })';
        
        return $html;
    }
    
    function get_colors()
    {
        if(!strlen($this->pivot_table['colors'])) return '';
        
        $colors = [];
        
        foreach(explode(',',$this->pivot_table['colors']) as $color)
        {
            if(strlen($color))
            {
                $colors[] = substr($color,0,7);
            }
        }
        
        return count($colors) ? "'" . implode("','", $colors). "'":'';
    }
    
    function get_cahrt_color_css()
    {
        if(!strlen($this->pivot_table['colors'])) return '';
        
        $yaxis_color = strlen($this->pivot_table['colors']) ? explode(',',$this->pivot_table['colors']) : [];
        
        $html = '<style>';
        foreach($yaxis_color as $k=>$v)
        {
            if(!strlen($v)) continue;
            
            $html .= '
                #pivot_table_' . $this->pivot_table['id'] . '_chart .highcharts-color-' . $k . ' {
                    fill: ' . $v . ';
                    stroke: ' . $v . ';
                }

                ';
        }
        
        $html .= '</style>';
        
        return $html;
    }
        
       
    
    function get_height()
    {
        return ($this->pivot_table['height']>0 ? $this->pivot_table['height'] : 600);
    }
    
    function get_chart_height()
    {
        return ($this->pivot_table['chart_height']>0 ? $this->pivot_table['chart_height'] : $this->get_height());
    }
    
    function has_toolbar()
    {
        global $app_module_path;
        
        if($app_module_path!='ext/pivot_tables/view')
        {
            return 0;
        }
        else
        {
            return 1;
        }
    }
    
    function get_localization()
    {
        $file = 'js/webdatarocks/' . $this->version . '/languages/' . APP_LANGUAGE_SHORT_CODE .'.json';
        if(is_file($file))
        {
            return $file;
        }
        else
        {
            return 'js/webdatarocks/' . $this->version . '/languages/en.json';
        }
    }
    
    function getReport()
    {
        global $app_user;
                        
        $use_user_id = (($app_user['group_id']==0 or !$this->has_access('full'))? 0 : $app_user['id']);
        
        $settings_query = db_query("select * from  app_ext_pivot_tables_settings where length(settings)>0 and reports_id='" . $this->id . "' and users_id='" . $use_user_id . "'");
        if($settings = db_fetch_array($settings_query))
        {
            $report = json_decode($settings['settings'],true);
            $report['dataSource']['filename'] = url_for('ext/pivot_tables/view','action=get_csv&id=' . $this->id);
            
            return json_encode($report);
        }
        else
        {
            $report = [
              'dataSource' => [
                  'dataSourceType' => 'csv',
                  'filename' => url_for('ext/pivot_tables/view','action=get_csv&id=' . $this->id),
              ]  
            ];
            
            return json_encode($report);
        }
        
    }
    
    function hide_actions_in_toolbar()
    {
        $html = '';
        if(!$this->has_access('full'))
        {
            $html = '
                <style>
                #wdr-toolbar-wrapper #wdr-toolbar li#wdr-tab-format,
                #wdr-toolbar-wrapper #wdr-toolbar li#wdr-tab-options,
                #wdr-toolbar-wrapper #wdr-toolbar li#wdr-tab-fields{
                    display:none;
                }
                </style>
                
                ';
        }
        
        return $html;
    }
    
    function has_access($access=false)
    {
        global $app_user;
        
        if($app_user['group_id']==0) return true;
        
        if(strlen($this->pivot_table['users_groups']))
        {
            $users_groups = json_decode($this->pivot_table['users_groups'],true);
            
            if(!$access)
            {
                if(isset($users_groups[$app_user['group_id']]))
                {
                    return (strlen($users_groups[$app_user['group_id']]) ? true : false);
                }
            }
            else
            {
                if(isset($users_groups[$app_user['group_id']]))
                {
                    return ($users_groups[$app_user['group_id']]==$access ? true : false);
                }
            }
        }
        
        return false;
    }
    
    function get_fiters_reports_id()
    {                
        return default_filters::get_reports_id($this->entities_id, 'default_pivot_tables' . $this->id);               
    }
    
    function get_fields_by_entity($entities_id)
    {
        $reports_fields = array();
        $reports_fields_names = array();
        $reports_fields_dates_format = array();
        $pivotreports_fields_query = db_query("select * from app_ext_pivot_tables_fields where reports_id='" . db_input($this->id) . "' and entities_id='" . db_input($entities_id) . "'");
        while($pivotreports_fields = db_fetch_array($pivotreports_fields_query))
        {
            $reports_fields[] = $pivotreports_fields['fields_id'];
            
            if(strlen($pivotreports_fields['fields_name'])>0)
            {
                $reports_fields_names[$pivotreports_fields['fields_id']] = $pivotreports_fields['fields_name'];
            }
            
            if(strlen($pivotreports_fields['cfg_date_format'])>0)
            {
                $reports_fields_dates_format[$pivotreports_fields['fields_id']] = $pivotreports_fields['cfg_date_format'];
            }
        }
        
        return array(
            'reports_fields'=>$reports_fields,
            'reports_fields_names'=>$reports_fields_names,
            'reports_fields_dates_format'=>$reports_fields_dates_format,
        );
    }
    
    static function array_to_csv($output)
    {
        return implode(',',$output) . "\n";
    }
    
    static function css_prepare($output)
    {
        return '"' . str_replace('"','""',trim(strip_tags($output))) . '"';        
    }
    
    static function prepare_csv_output_for_parent_entities($output_array,$parent_entities_listing_fields,$parrent_entities,$parent_item_id, $fields_dates_format)
    {
        
        foreach($parrent_entities as $entities_id)
        {
            //prepare forumulas query
            $listing_sql_query_select = fieldtype_formula::prepare_query_select($entities_id); //,'',false,['fields_in_query'=>implode(',',$parent_entities_listing_fields[$entities_id])]);
            
            $items_sql_query = "select * {$listing_sql_query_select} from app_entity_" . $entities_id . " e where id ='" . $parent_item_id . "'";
            $items_query = db_query($items_sql_query);
            if($item = db_fetch_array($items_query))
            {
                if(isset($parent_entities_listing_fields[$entities_id]))
                {
                    foreach($parent_entities_listing_fields[$entities_id] as $field)
                    {
                        $value = items::prepare_field_value_by_type($field, $item);
                        
                        if(in_array($field['type'],array('fieldtype_date_added','fieldtype_input_date','fieldtype_input_datetime')) and isset($fields_dates_format[$field['id']]))
                        {
                            $output_array[] = pivot_tables::css_prepare(i18n_date($fields_dates_format[$field['id']],$value));
                        }
                        else
                        {
                            $output_options = array('class'=>$field['type'],
                                'value'=>$value,
                                'field'=>$field,
                                'item'=>$item,
                                'is_export'=>true,
                                'reports_id'=> 0,
                                'path'=> '',
                                'path_info' => '');
                            
                            $output_array[] = pivot_tables::css_prepare(fields_types::output($output_options));
                        }
                    }
                }
                
                $parent_item_id = $item['parent_item_id'];
            }
        }
        
        return $output_array;
    }
    
}