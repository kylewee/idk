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

class graphicreport
{
    public $report;
    
    function __construct($report)
    {
        $this->report = $report;
    }
    
    function render_plot_options()
    {
        if($this->report['show_totals']==0) return '';
        
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
                            return Number.isInteger(this.y) ? this.y : Highcharts.numberFormat(this.y,2);
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
                            return Number.isInteger(this.y) ? this.y : Highcharts.numberFormat(this.y,2);
                        }
                    },
                }
            },    
       ";
        
        return $html;
    }
    
    function remove_zero_values($xaxis, $yaxis)
    {
        if($this->report['hide_zero']==0)
        {
             return [
                'xaxis' => $xaxis,
                'yaxis' => $yaxis,
            ];
        }
        
        //print_rr($yaxis);
        foreach($xaxis as $xkey => $date)
        {
            $date = substr($date,1,-1);
            
            $is_zero = true;
            
            //check if zero
            foreach($yaxis as $field_id=>$data)
            {                         
                if(isset($data[$date]) and !strstr($data[$date],'y:0,'))
                {
                    $is_zero = false;
                }                
            }
            
            //remove zero values
            if($is_zero)
            {
                unset($xaxis[$xkey]); 
                
                foreach($yaxis as $field_id=>$data)
                {
                    if(isset($yaxis[$field_id][$date]))
                    {
                        unset($yaxis[$field_id][$date]);
                    }
                }
            }            
        }
        
        return [
            'xaxis' => $xaxis,
            'yaxis' => $yaxis,
        ];
    }
    
    function get_cahrt_color_css($yaxis_color)
    {                
        $html = '<style>';
        $k =0;
        foreach($yaxis_color as $v)
        {
            if(strlen($v))
            {            
                $html .= '
                    #graphicreport_container' . $this->report['id'] . ' .highcharts-color-' . $k . ' {
                        fill: ' . $v . ';
                        stroke: ' . $v . ';
                    }

                    ';
            }
            
            $k++;
        }
        
        $html .= '</style>';
        
        return $html;
    }
}
