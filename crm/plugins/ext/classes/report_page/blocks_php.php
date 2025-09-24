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

class blocks_php
{
    private $block,
            $report,
            $settings,
            $item;
    
    function __construct($block, $report, $item)
    {
        $this->block = $block;
        $this->report = $report;
        
        $this->settings = new \settings($this->block['settings']);  
        
        $this->item = $item;
    }
    
    function render()
    {
        global $app_entities_cache, $app_fields_cache, $app_user, $app_choices_cache, $app_users_cache, $app_global_choices_cache, $app_access_groups_cache, $report_page_filters, $current_item_id;
                
                        
        $php_code = $this->settings->get('php_code');
        
        if(!strlen($php_code)) return '';
        
        for($i=1;$i<=report_filters::COUNT_EXTRA_FILTERS;$i++)
        {
            $php_code = str_replace('[filter_by_entity' . $i . ']',$report_page_filters[$this->report['id']]['filter_by_entity' . $i]??0,$php_code);
        }
        
        for($i=1;$i<=report_filters::COUNT_EXTRA_FILTERS;$i++)
        {
            $php_code = str_replace('[filter_by_list' . $i . ']',$report_page_filters[$this->report['id']]['filter_by_list' . $i]??0,$php_code);
        }
        
        $php_code = str_replace([
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
                $app_user['id']??0,
                ],$php_code);
        
        try
        {                        
            eval($php_code);
        }
        catch (Error $e)
        {
            return  alert_error(TEXT_ERROR . ' ' . $e->getMessage() . ' on line ' . $e->getLine());
        }
              
        return (isset($output_value) ? $output_value : '');
    }
    
    static function render_helper($arg)
    {
        $arg = new \settings($arg);
        
        $html = '';
        switch($arg->get('type'))
        {
            case 'item':
                $html = self::render_item_helper($arg->get('entity_id'));
                break;
            case 'total':
                $html = self::render_total_helper($arg->get('block_id'));
                break;
        }
        
        return $html;
    }
    
    static function render_item_helper($entity_id)
    {
        $html = '
            <div class="dropdown">
                <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                  ' . TEXT_AVAILABLE_VALUES . '
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1" style="max-height: 250px; overflow-y: auto">
            ';
        
        $field_query = \fields::get_query($entity_id,"and f.type not in ('fieldtype_action')");
        while($field = db_fetch_array($field_query))
        {
            if(in_array($field['type'],\fields_types::get_reserved_types()))
            {
                $data_insert = '$item[\'' . str_replace('fieldtype_','',$field['type']) . '\']';
            }
            else
            {
                $data_insert = '$item[\'field_' . $field['id'] . '\']';
            }
            
            $html .= '
                        <li>
                                    <a href="#"  class="insert_to_php_code" data-field="' . $data_insert . '">' . \fields_types::get_option($field['type'], 'name', $field['name']) . ' ' . $data_insert . '</a>  		      
                        </li>';
        }
        
        $html .= '</ul></div>';
        
        
        $html .= '
            <script>
                $(".insert_to_php_code").click(function(){
                    insert_to_code_mirror("settings_php_code",$(this).attr("data-field"))
                })
            </script>
            ';
        
        return $html;
    }
    
    static function render_total_helper($block_id)
    {
        $html = '
            <div class="dropdown">
                <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                  ' . TEXT_COLUMNS . '
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1" style="max-height: 250px; overflow-y: auto">
            ';
        
        $blocks_query = db_query("select b.*,f.type as field_type, f.name as field_name, f.entities_id as field_entity_id from app_ext_report_page_blocks b left join app_fields f on b.field_id=f.id  where block_type='body_cell'  and b.parent_id = {$block_id} order by b.sort_order, b.id");
        while($blocks = db_fetch_array($blocks_query))
        {       
            $settings = new \settings($blocks['settings']);
            
            
            $data_insert = '$total[\'column_' . $blocks['id'] . '\']';
            
            
            $html .= '
                        <li>
                                    <a href="#"  class="insert_to_php_code" data-field="' . $data_insert . '">' . strip_tags($settings->get('heading')) . ' ' . $data_insert . '</a>  		      
                        </li>';
        }
        
        $html .= '</ul></div>';
        
        
        $html .= '
            <script>
                $(".insert_to_php_code").click(function(){
                    insert_to_code_mirror("settings_php_code",$(this).attr("data-field"))
                })
            </script>
            ';
        
        return $html;
    }
}
