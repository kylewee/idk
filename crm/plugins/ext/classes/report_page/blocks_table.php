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

namespace report_page;

/**
 * Description of blocks_table
 *
 * @author USER
 */
class blocks_table
{
    private $block,
            $report,
            $column_total,
            $chart_xaxis,
            $chart_yaxis,
            $item,
            $settings,
            $debug_mode,
            $chart_settigns;
    
    function __construct($block, $report, $item)
    {
        $this->block = $block;
        $this->report = $report;
        $this->chart_xaxis = [];
        $this->chart_yaxis = [];
        $this->column_total = [];
                
        $this->settings = new \settings($this->block['settings']);  
        
        $this->debug_mode = $this->settings->get('debug_mode')==1 ? true:false;
        
        $this->item = $item;
        
        $this->set_chart_settings();
    }
    
    function set_chart_settings()
    {        
        $number_format = strlen($this->settings->get('chart_number_format')) ? explode('/',$this->settings->get('chart_number_format')) : [];
        
        $this->chart_settigns = [
            'decimals' => $number_format[0]??2,
            'decimal_separator' => $number_format[1]??'.',
            'thousands_separator' => $number_format[2]??' ',
            'show_labels' => $this->settings->get('chart_show_labels'),
            'prefix' => $this->settings->get('chart_number_prefix'),
            'suffix' => $this->settings->get('chart_number_suffix'),
        ];
    }
    
    function has_chart()
    {
        return strlen($this->settings->get('chart_type')) ? true:false;
    }
    
    function render()
    {
        if($this->has_chart())
        {
            return $this->render_chart_layout();
        }
        else
        {
            return $this->render_table();
        }
    }
    
    function render_chart_layout()
    {
        $table_html = $this->render_table();
        
        switch($this->settings->get('chart_position'))
        {
            case 'right':
                $html = '
                    <div class="row">
                        <div class="col-md-6">
                            ' . $table_html . '
                        </div>        
                        <div class="col-md-6">
                            <div id="report_page_' . $this->block['id'] . '_chart" class="pivot_table_chart_side" style="height: ' . $this->get_chart_height() . 'px;"></div>                                
                        </div>        
                    </div>';
                break;
            case 'left':
                $html = '
                    <div class="row">
                        <div class="col-md-6">
                            <div id="report_page_' . $this->block['id'] . '_chart" class="pivot_table_chart_side" style="height: ' . $this->get_chart_height() . 'px;"></div>
                        </div>        
                        <div class="col-md-6">
                            ' . $table_html . '
                        </div>                                    
                    </div>';
                break;
            case 'top':
                $html = ''
                    . '<div id="report_page_' . $this->block['id'] . '_chart" style="height: ' . $this->get_chart_height() . 'px;"></div>'
                    . $table_html;
                break;
            case 'bottom':
                $html = ''
                    . $table_html
                    . '<div id="report_page_' . $this->block['id']. '_chart" style="height: ' . $this->get_chart_height() . 'px;"></div>';
                break;
            case 'only_chart':
                $html = ''
                    . '<div class="pivot_table_bar" id="pivot_table_bar_' . $this->report['id'] . '">
                         <div class="pivot_table_bar_action"></div>
                         <div class="scroller">' . $table_html . '</div>
                       </div>'
                    . '<div id="report_page_' . $this->block['id'] . '_chart" style="height: ' . $this->get_chart_height() . 'px;"></div>';
                
                $html .= '
                    <script>
                    $("#pivot_table_bar_' . $this->block['id'] . ' .pivot_table_bar_action").click(function(){
                        if($(this).hasClass("expanded"))
                        {
                            $(this).removeClass("expanded")
                            $(this).parent().css({height:"15px"})
                        }
                        else
                        {
                            $(this).addClass("expanded")
                            $(this).parent().css({height:"' . $this->get_chart_height() . 'px",position: "absolute",width:"100%",oveflow: "auto"})
                            $("#report_page_' . $this->block['id']. '_chart").css("padding-top","15px")
                        }
                    })
                    </script>
                    ';
                        
                break;
        }
        
        $html .= $this->render_chart();
        
        return $html;
    }
    
    function get_chart_height()
    {
        return $this->settings->get('chart_height')>0 ? $this->settings->get('chart_height') : 600;
    }
    
    function get_chart_type()
    {
        $type = $this->settings->get('chart_type');
        
        switch($this->settings->get('chart_type'))
        {
            case 'stacked_column':
            case 'stacked_percent':
                $type =  'column';
                break;
            case 'stacked_area':
                $type = 'area';
                break;        
        }
        
        return $type;
    }
    
    function get_chart_labels_rotation()
    {        
        switch($this->settings->get('chart_type'))
        {            
            case 'bar':
                return '0';
                break;
            default:
                return '-90';
                break;
            
        }
    }
    
    function render_chart()
    {
        //print_rr($this->chart_xaxis);
        //print_rr($this->chart_yaxis);
        
        $yaxis_color = is_array($this->settings->get('chart_colors')) ? $this->settings->get('chart_colors') : [];
        $chart_types = is_array($this->settings->get('chart_types')) ? $this->settings->get('chart_types') : [];
        
        $series = [];
        $series_grouped = [];        
        
        $count = 0;
        foreach ($this->chart_yaxis as $block_id => $data)
        {
            
            $block = db_find('app_ext_report_page_blocks',$block_id);
            $s = new \settings($block['settings']);
            
            $type = ((isset($chart_types[$count]) and strlen($chart_types[$count])) ? $chart_types[$count] : $this->get_chart_type());
            
            if(count($data)==1 and in_array($type,['pie','funnel','pyramid']))
            {                                        
                $series_grouped[$type][] = implode(',',$data);
            }
            else
            {              
                $series[] = '{
                    type: "' . $type . '",
                    name:"' . addslashes($s->get('heading')) . '",        
                    data:[' . implode(',', $data) . '],
                    ' . ((isset($yaxis_color[$count]) and strlen($yaxis_color[$count])) ? 'color: "' . $yaxis_color[$count] . '"' : '') . '
                  }';
            }
            
            $count++;
        }
        
        //print_rr($series);
        //print_rr($series_grouped);
        
        if(count($series_grouped))
        {
            foreach($series_grouped as $type=>$data)
            $series[] = '{
                    type: "' . $type . '",
                    name:"",        
                    data:[' . implode(',', $data) . '],                    
                  }';
        }
                        
        
        $html = '
            <script type="text/javascript">

    $(function () {
        $("#report_page_' .  $this->block['id'] . '_chart").highcharts(         
            {
            chart: {
                type: "' . $this->get_chart_type() . '",
                styledMode: true    
            },
            title: {
                text: "' . (count($this->chart_yaxis) == 0 ? TEXT_NO_RECORDS_FOUND : "")  . '"
            },
            subtitle: {
                text: ""
            },
            xAxis: {
                categories: [' .  implode(',', $this->chart_xaxis) . '],
                labels: {
                    rotation: ' . $this->get_chart_labels_rotation() . '
                }
            },
            yAxis: {
                title: {
                    text: ""
                },                
                labels: {
                    formatter: function () {
                        return this.axis.defaultLabelFormatter.call(this);
                    }
                }
            },
            
            ' . $this->get_cahrt_plot_options() . ' 
                
            tooltip: {
                formatter: function () {
                    //console.log(this.point)
                    if(this.x)
                    {                                                                                                
                        let value =  "' . $this->chart_settigns['prefix'] . '"+Highcharts.numberFormat(this.point.y,' . $this->chart_settigns['decimals'] . ',"' . $this->chart_settigns['decimal_separator'] . '","' . $this->chart_settigns['thousands_separator'] . '")+"' . $this->chart_settigns['suffix'] . '";
                        
                        return \'<span style="font-size: 10px;">\' + this.x + \'</span><br><b>\' + this.series.name + \': </b>\' + value + (this.point.percentage ? " "+Math.round(this.point.percentage)+"%" : "");
                    }
                    else
                    {
                        return \'<b>\' + (this.series.name!="" ? this.series.name : this.point.name) + \': </b>\' + this.point.y;
                    }
                }
            },

            series: [' .  implode(',', $series) . ']
        })
    });

</script>';
        
    $html .= $this->get_cahrt_color_css();    
        
        return $html;
    }
    
    function get_cahrt_color_css()
    {
        $yaxis_color = is_array($this->settings->get('chart_colors')) ? $this->settings->get('chart_colors') : [];
        
        $html = '<style>';
        foreach($yaxis_color as $k=>$v)
        {
            if(!strlen($v)) continue;
            
            $html .= '
                #report_page_' .  $this->block['id'] . '_chart .highcharts-color-' . $k . ' {
                    fill: ' . $v . ';
                    stroke: ' . $v . ';
                }

                ';
        }
        
        $html .= '</style>';
        
        return $html;
    }
    
    function get_cahrt_plot_options()
    {
        $html = '';
        
        if($this->settings->get('chart_type')=='stacked_column')
        {
            $html = "
                plotOptions: {
                    column: {
                      stacking: 'normal'
                    }
                  },
                ";
            
        }
        elseif($this->settings->get('chart_type')=='stacked_percent')
        {
            $html = "
                plotOptions: {
                    column: {
                      stacking: 'percent'
                    }
                  },
                ";
        }     
        elseif($this->settings->get('chart_type')=='stacked_area')
        {
            $html = "
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
                },
                ";
            
        }
        elseif($this->settings->get('chart_show_totals')==1)
        {                        
            $html = "
                plotOptions: {
                    column: {
                        dataLabels: {
                            enabled: true,
                            rotation: -90,
                            align: 'left',
                            style: {
                                fontFamily: 'Arial, sans-serif',
                                fontWeight: 'normal',
                                fontSize: '11px;',
                            },
                            formatter: function () {                                
                                return '" . $this->chart_settigns['prefix'] . "'+Highcharts.numberFormat(this.y," . $this->chart_settigns['decimals'] . ",'" . $this->chart_settigns['decimal_separator'] . "','" . $this->chart_settigns['thousands_separator'] . "')+'" . $this->chart_settigns['suffix'] . "';
                            }                        
                        }
                    },
                    line: {
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontFamily: 'Arial, sans-serif',
                                fontWeight: 'normal',
                                fontSize: '11px;',
                            },
                            formatter: function () {
                                return '" . $this->chart_settigns['prefix'] . "'+Highcharts.numberFormat(this.y," . $this->chart_settigns['decimals'] . ",'" . $this->chart_settigns['decimal_separator'] . "','" . $this->chart_settigns['thousands_separator'] . "')+'" . $this->chart_settigns['suffix'] . "';
                            }
                        },
                    },
                    spline: {
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontFamily: 'Arial, sans-serif',
                                fontWeight: 'normal',
                                fontSize: '11px;',
                            },
                            formatter: function () {
                                return '" . $this->chart_settigns['prefix'] . "'+Highcharts.numberFormat(this.y," . $this->chart_settigns['decimals'] . ",'" . $this->chart_settigns['decimal_separator'] . "','" . $this->chart_settigns['thousands_separator'] . "')+'" . $this->chart_settigns['suffix'] . "';
                            }
                        },
                    }
                },    
           ";
        }
        
        return $html;
    }
    
    function prepare_chart_data($item_count,$item_row, $item_count_total)
    {
        
        $xaxis_block_id =$this->settings->get('chart_xaxis');
        $xaxis_value = isset($item_row[$xaxis_block_id]) ? $item_row[$xaxis_block_id]['value']:'';
        $xaxis_name = isset($item_row[$xaxis_block_id]) ? $item_row[$xaxis_block_id]['name']:'';
        $this->chart_xaxis[] = '"' . $xaxis_value . '"';
        
        $yaxis_color = is_array($this->settings->get('chart_colors')) ? $this->settings->get('chart_colors') : [];
                        
        if(is_array($this->settings->get('chart_yaxis')))
        {                   
            foreach($this->settings->get('chart_yaxis') as $yaxis_block_id)
            {
                $value = isset($item_row[$yaxis_block_id]) ? $item_row[$yaxis_block_id]['value']:'';                               
                $value = $this->prepare_number_value($value);

                $name =  isset($item_row[$yaxis_block_id]) ? $item_row[$yaxis_block_id]['name']:'';
                if($item_count_total>1)
                {
                    switch($this->settings->get('chart_type'))
                    {
                        case 'pie':
                        case 'funnel':
                        case 'pyramid':
                            $name = $xaxis_value; 
                            break;
                    }
                }

                $this->chart_yaxis[$yaxis_block_id][] = '{
                    y:' . $value. ',
                    name:"' . addslashes($name) . '",                    
                    ' . ((isset($yaxis_color[$item_count-1]) and strlen($yaxis_color[$item_count-1])) ? 'color: "' . $yaxis_color[$item_count-1] . '"' : '') . '    
                    }';                                                
            }
        }
    }
    
    function prepare_number_value($value)
    {
        $is_negative = substr($value,0,1)=='-' ? true : false;
        
        $value = strlen($value) ? preg_replace("/[^0-9.]/","",$value) : 0;
        $value = preg_replace("/\.+/",".",$value);
        $value = preg_replace("/^\./","",$value);
        $value = preg_replace("/\.$/","",$value);
        
        
        $value = $is_negative ? (0-$value) : $value;
        
        return (float)$value;
    }    
    
    function get_table_height()
    {
        $height = $this->settings->get('table_height');
        
        if($this->settings->get('chart_position')=='only_chart')
        {
            $height = $this->get_chart_height();
        }
        
        return $height;
    }
    
    function render_table()
    {        
        if(!strlen($this->settings->get('mysql_query'))) return '';
        
        $export_data = [];
        
        $starttime = microtime(true);
        
        $table_attributes = strlen($this->settings->get('tag_table_attributes')) ? $this->settings->get('tag_table_attributes') : 'class="table table-striped table-bordered table-hover ' . ($this->get_table_height()>0 ? 'table-thead-sticky table-tfoot-sticky':'' ). '"';
        
        $html = '
        <div ' . ($this->get_table_height()>0 ? 'class="scroller" data-height="' . $this->get_table_height() . '"':'' ) . '>
            <table id="report_page_table_block' .  $this->block['id'] . '" ' . $table_attributes . '>
                <thead>';
        
        
        
        //extra rows
        $rows_query = db_query("select b.* from app_ext_report_page_blocks b where b.block_type='thead' and b.report_id = " . $this->report['id'] . " and b.parent_id = " . $this->block['id'] . " order by b.sort_order, b.id");
        while($rows = db_fetch_array($rows_query))
        {
            $blocks_query = db_query("select b.* from app_ext_report_page_blocks b where b.report_id = " . $this->report['id'] . " and b.parent_id = " . $rows['id'] . " order by b.sort_order, b.id");

            if(db_num_rows($blocks_query))
            {
                $export_row = [];
                
                $html .= '<tr>';

                while($blocks = db_fetch_array($blocks_query))
                {
                    $settings = new \settings($blocks['settings']);

                    $cell_value = $settings->get('heading');                                               
                    $cell_settings = $settings->get('tag_td_attributes');

                    $html .= '<th ' . $cell_settings . '>' . $cell_value . '</th>';
                    
                    $export_row[] = $cell_value;
                }

                $html .= '</tr>';
                
                $export_data[] = $export_row;
            }    
        }  
        
        //export button
        $export_button_html = '';
        if($this->settings->get('xls_export')==1 and ($this->report['type']!='print' or $this->report['entities_id']==0))
        {
            $export_button_html = '                
                        <a title="' . TEXT_EXPORT . '"  href="javascript: $(\'#report_page_export_block' . $this->block['id'] . '\').submit();"  class="table-xls-export noprint"><i class="fa fa-download"></i></a>                    
                        ';
            
            $export_link_html = '                
                        <a title="' . TEXT_EXPORT . '"  href="javascript: $(\'#report_page_export_block' . $this->block['id'] . '\').submit();" class="table-xls-export-link noprint" ><i class="fa fa-download"></i></a>                    
                        ';
        }
        
        //thead
        $export_row = [];
        $colspan_number = 0;
        
        $html .= '<tr>';

        if($this->settings->get('line_numbering')==1)
        {    
            $html .= '<th class="line_numbering_heading">' . $this->settings->get('line_numbering_heading') . '</th>';
            $colspan_number++;
            
            $export_row[] = $this->settings->get('line_numbering_heading');
        }
        
        $blocks_query = db_query("select b.* from app_ext_report_page_blocks b where block_type='body_cell'  and b.parent_id = " . $this->block['id'] . " order by b.sort_order, b.id");
        $count_blocks = db_num_rows($blocks_query);
        $count = 1;
        while($blocks = db_fetch_array($blocks_query))
        {
            $settings = new \settings($blocks['settings']); 
                        
            $html .= '<th ' . $settings->get('tag_td_attributes')  . '>' . $settings->get('heading') . 
                    (($count_blocks==$count and ($this->settings->get('pagination')!=1 or ($this->settings->get('pagination')==1 and $this->settings->get('sort_values')!=1))) ? $export_button_html : '') .  
                    '</th>';
            
            $export_row[] = $settings->get('heading');
            
            $colspan_number++;
            
            $this->column_total['column_' . $blocks['id']] = 0;
            
            $count++;
        }
        $html .= '</tr>';
        
        $export_data[] = $export_row;

                
        //column numbering
        if($this->settings->get('column_numbering')==1)
        {
            $export_row = [];
            $html .= '<tr class="column_numbering">';

            $count = 1;

            if($this->settings->get('line_numbering')==1)
            {
                $html .= '<td>' . $count . '</td>';
                $export_row[] = $count;
                
                $count++;
            }

            $blocks_query = db_query("select b.* from app_ext_report_page_blocks b  where block_type='body_cell'  and b.parent_id = " . $this->block['id'] . " order by b.sort_order, b.id");
            while($blocks = db_fetch_array($blocks_query))
            {
                $settings = new \settings($blocks['settings']);
                
                $html .= '<td>' . $count . '</td>';
                $export_row[] = $count;
                
                $count++;                                
            }
            $html .= '</tr>';
            
            $export_data[] = $export_row;
        }
                        
        $html .= '
            </thead>
            <tbody>    
        ';
        
        $item_count = 1;
        $item_query = db_query($this->prepare_filters($this->settings->get('mysql_query')), $this->debug_mode);
        $item_count_total = db_num_rows($item_query);
        while($item = db_fetch_array($item_query))
        {
            //print_rr($item);
            
            $export_row = [];
            
            $html .= '<tr>';
            
            //line numbering count
            if($this->settings->get('line_numbering')==1)
            {
                $html .= '<td class="line_numbering">' . $item_count .  '</td>';
                $export_row[] = $item_count;
            }
            
            $item_row = [];
            
            $blocks_query = db_query("select b.*,f.type as field_type, f.name as field_name, f.entities_id as field_entity_id from app_ext_report_page_blocks b left join app_fields f on b.field_id=f.id  where block_type='body_cell'  and b.parent_id = " . $this->block['id'] . " order by b.sort_order, b.id");
            while($blocks = db_fetch_array($blocks_query))
            {                                        
                $settings = new \settings($blocks['settings']);
                
                $column = trim($settings->get('column'));
                $value = $chart_value = (strlen($column) and isset($item[$column])) ? $item[$column] : '';
                $data_sort_attr = '';
                
                switch($settings->get('value_type'))
                {
                    case 'text':
                        $value = $this->prepare_field_value($value,$column);
                        break;
                    case 'numeric':
                        
                        if(strlen($value) and is_numeric($value))
                        {
                            $this->column_total['column_' . $blocks['id']] += $value;            
                        }
                        
                        $data_sort_attr = 'data-sort="' . $value . '"';
                        
                        //apply number format
                        if(strlen($settings->get('number_format'))>0 and is_numeric($value))
                        {
                            $format = explode('/',str_replace('*','',$settings->get('number_format')));
                            
                            $value = number_format($value,$format[0],$format[1],$format[2]);
                        }
                        
                        if(strlen($value))
                        {
                            $value = $settings->get('prefix') . $value . $settings->get('suffix');
                        }
                        break;
                    case 'date':                        
                        $value = $chart_value = (strlen($value) and $value>0) ? format_date($value, $settings->get('date_format')):'';
                        break;
                    case 'php_code':
                        $value = $chart_value = $this->render_body_cell_php_value($item, $blocks);
                        
                        $data_sort_attr = 'data-sort="' . htmlspecialchars(strip_tags($value)) . '"';
                        
                        //apply number format
                        if(strlen($settings->get('number_format'))>0 and is_numeric($value))
                        {
                            $format = explode('/',str_replace('*','',$settings->get('number_format')));
                            
                            $value = number_format($value,$format[0],$format[1],$format[2]);
                        }
                        
                        $value = $settings->get('prefix') . $value . $settings->get('suffix');
                        
                        break;                    
                }
                
                $html .= '<td ' . $settings->get('tag_td_attributes') . ' ' . $data_sort_attr . '>' . $value . '</td>';
                $export_row[] = strip_tags($value);
                
                $item_row[$blocks['id']] = ['name'=>$settings->get('heading'),'value'=>$chart_value];
                                
            }
            
            if($this->has_chart())
            {
                $this->prepare_chart_data($item_count,$item_row, $item_count_total);
            }
            
            
            $item_count++;
            
            $html .= '</tr>';
            
            $export_data[] = $export_row;
        }
        
        if($item_count==1)
        {
            $html .= '<tr><td colspan="' . $colspan_number . '">' . TEXT_NO_RECORDS_FOUND . '</td></tr>';
        }
        
        
        $html .='
                </tbody>
                <tfoot>
        ';
        
        
        //print_rr($this->column_total);
        
        $rows_query = db_query("select b.* from app_ext_report_page_blocks b where b.block_type='tfoot' and b.report_id = " . $this->report['id'] . " and b.parent_id = " . $this->block['id'] . " order by b.sort_order, b.id");
        while($rows = db_fetch_array($rows_query))
        {
            $blocks_query = db_query("select b.* from app_ext_report_page_blocks b where b.report_id = " . $this->report['id'] . " and b.parent_id = " . $rows['id'] . " order by b.sort_order, b.id");

            if(db_num_rows($blocks_query))
            {
                $export_row = [];
                $html .= '<tr>';

                while($blocks = db_fetch_array($blocks_query))
                {
                    $settings = new \settings($blocks['settings']);

                    $cell_value = $settings->get('heading');                                               
                    $cell_settings = $settings->get('tag_td_attributes');
                    
                    switch($settings->get('value_type'))
                    {                        
                        case 'php_code':                            
                            $cell_value = $this->render_body_cell_php_value($this->item, $blocks, $this->column_total);
                            //apply number format
                            if(strlen($settings->get('number_format'))>0 and is_numeric($cell_value))
                            {
                                $format = explode('/',str_replace('*','',$settings->get('number_format')));

                                $cell_value = number_format($cell_value,$format[0],$format[1],$format[2]);
                            }

                            $cell_value = $settings->get('prefix') . $cell_value . $settings->get('suffix');
                            break;                      
                    }

                    $html .= '<td ' . $cell_settings . '>' . $cell_value . '</td>';
                    
                    $export_row[] = $cell_value;
                }

                $html .= '</tr>';
                
                $export_data[] = $export_row;
            }    
        }                         
        
        
        $html .= '
                <tfoot>
            </table>
        </div>';
        
        //print_rr($export_data);
        
        if(count($export_data))
        {
            $html .= form_tag('report_page_export_block' . $this->block['id'] ,url_for('report_page/view','id=' . $this->report['id'] . '&action=xls_export')) . input_hidden_tag('export_data', json_encode($export_data)) . '</form>';
        }
        
        if($this->settings->get('pagination')==1 and $item_count>1)
        {
            $html .= '
                <script>
                    $(function(){
                        $("#report_page_table_block' .  $this->block['id'] . '").dataTable({
                            language: {
                                "info": "' . sprintf(TEXT_DISPLAY_NUMBER_OF_ITEMS, '_START_', '_END_', '_TOTAL_') . '",
                                "paginate": {
                                  "next": "<i class=\"fa fa-angle-right\"></i>",
                                  "previous": "<i class=\"fa fa-angle-left\"></i>"
                                },
                                "search": "' . TEXT_SEARCH . '"
                            },
                            dom: "<\'row\'<\'col-sm-6\'l><\'col-sm-6 dataTables-filters-right\'f>>" +
                            "<\'row\'<\'col-sm-12\'tr>>" +
                            "<\'row\'<\'col-sm-5\'i><\'col-sm-7\'p>>",                            
                            ordering: ' . ($this->settings->get('sort_values')==1 ? 'true':'false') . ',
                            lengthChange: false, 
                            searching: ' . ($this->settings->get('allow_search')==1 ? 'true':'false') . ',
                            pageLength: ' . (int)$this->settings->get('rows_per_page') . ',
                            order: [],
                            stateSave: false, 
                            fixedHeader: {
                                headerOffset: 50
                            }
                        })
                                               
                    })
                </script>
            ';
            
            if($this->settings->get('sort_values')==1)
            {
                if($this->settings->get('allow_search')==1 and strlen($export_link_html))
                {
                    $html .= '
                     <script>
                         $(function(){
                             $("#report_page_table_block' . $this->block['id'] . '_wrapper .dataTables_filter").append("<lable>' . addslashes(trim($export_link_html)) . '</lable>")                             
                         })
                     </script>
                    ';    
                }
                elseif(strlen($export_link_html))
                {
                    $html .= '
                     <script>
                         $(function(){
                             $("#report_page_table_block' . $this->block['id'] . '_wrapper .dataTables-filters-right").append("<lable>' . addslashes(trim($export_button_html)) . '</lable>")                             
                         })
                     </script>
                    ';  
                }
            }
        }
        
        $time = number_format((microtime(true) - $starttime), 4);
        
        if($this->debug_mode)
        {        
             $html .= '<div class="alert alert-warning">' . TEXT_TIME . ': ' . $time . '</div>';
        }
                
        return $html;
    }
    
    function prepare_field_value($value,$column)
    {        
        if(!strstr($column,'field_'))
        {
            return $value;
        }
        
        $field_id = str_replace('field_','',$column);
        
        $field_query = db_query("select * from app_fields where id='" . $field_id . "'");
        if(!$field = db_fetch_array($field_query))
        {
            return $value;
        }
        
        $cfg = new \fields_types_cfg($field['configuration']);
        
        switch($field['type'])
        {
            case 'fieldtype_input_encrypted':
            case 'fieldtype_textarea_encrypted':
                $value =  \fieldtype_input_encrypted::decrypt_value($value);
                break;
            case 'fieldtype_dropdown':
            case 'fieldtype_checkboxes':
            case 'fieldtype_color':
            case 'fieldtype_dropdown_multilevel':
            case 'fieldtype_dropdown_multiple':
            case 'fieldtype_radioboxes':
            case 'fieldtype_stages':
            case 'fieldtype_tags':
                if(strlen($value))
                {
                    if($cfg->get('use_global_list')>0)
                    {
                        $value = \global_lists::render_value($value);
                    }
                    else
                    {
                        $value = \fields_choices::render_value($value);
                    }
                }                
                break;            
            case 'fieldtype_entity':
            case 'fieldtype_entity_ajax':
            case 'fieldtype_entity_multilevel':
            case 'fieldtype_users':
            case 'fieldtype_users_ajax':
                if(in_array($field['type'],['fieldtype_users','fieldtype_users_ajax']))
                {
                    $entity_id = 1;
                }
                else
                {
                    $entity_id = $cfg->get('entity_id');
                }
                
                if(strlen($value))
                {
                    $values = [];
                    foreach(explode(',',$value) as $item_id)
                    {
                        $values[] = \items::get_heading_field($entity_id, $item_id);
                    }
                    
                    $value = implode(', ', $values);
                }
                break;
        }
        
        return $value;        
    }
    
    function render_body_cell_php_value($item, $blocks, $total = [])
    {
        global $app_entities_cache, $app_fields_cache, $app_user, $app_users_cache, $app_choices_cache, $app_global_choices_cache, $app_access_groups_cache, $report_page_filters;
        
        $settings = new \settings($blocks['settings']);
        
        $php_code = $settings->get('php_code');
        
        $php_code  = str_replace([
            '[filter_by_date]',
            '[filter_by_user]',
            '[current_user_id]',            
            ],[
                $report_page_filters[$this->report['id']]['filter_by_date']??'',
                $report_page_filters[$this->report['id']]['filter_by_user']??'',
                $app_user['id']                
                ],$php_code);
        
        try
        {                        
            eval($php_code);
        }
        catch (Error $e)
        {
            echo alert_error(TEXT_ERROR . ' ' . $e->getMessage() . ' on line ' . $e->getLine());
        }
        
        if(isset($output_value) and is_numeric($output_value) and !count($total))
        {            
            $this->column_total['column_' . $blocks['id']] += $this->prepare_number_value($output_value);            
        }
        
        return (isset($output_value) ? $output_value : '');
    }
    
    function prepare_filters($sql)
    {
        global $report_page_filters, $current_item_id, $app_user;
        
        for($i=1;$i<=report_filters::COUNT_EXTRA_FILTERS;$i++)
        {
            $sql = str_replace('[filter_by_entity' . $i . ']',$report_page_filters[$this->report['id']]['filter_by_entity' . $i]??0,$sql);
        }
        
        for($i=1;$i<=report_filters::COUNT_EXTRA_FILTERS;$i++)
        {
            $sql = str_replace('[filter_by_list' . $i . ']',$report_page_filters[$this->report['id']]['filter_by_list' . $i]??0,$sql);
        }
        
        return str_replace([
            '[filter_by_date]',
            '[filter_by_date_from]',
            '[filter_by_date_to]',
            '[filter_by_user]',
            '[current_item_id]',
            '[current_user_id]'
            ],[
                $report_page_filters[$this->report['id']]['filter_by_date']??'',
                $report_page_filters[$this->report['id']]['filter_by_date_from']??'',
                $report_page_filters[$this->report['id']]['filter_by_date_to']??'',
                $report_page_filters[$this->report['id']]['filter_by_user']??'',
                $this->item['id']??0,
                $app_user['id']
                ],$sql);
    }
}
