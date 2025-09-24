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

class blocks_dropdown
{
    public $report_id;
    
    function __construct($report_id)
    {
        $this->report_id = $report_id;        
    }
    
    function render()
    {
        $html = '
            <ul class="list-inline">
                <li>' . TEXT_INSERT . ':</li>                
                <li>' . $this->render_blocks() . '</li>                    
            </ul>
            ';
        
        return $html;
    }    
    
    function render_blocks()
    {      
        global $app_entities_cache;
        
        $block_html = '';                
        $block_query = db_query("select b.*,f.type as field_type, f.name as field_name, f.entities_id as field_entity_id from app_ext_report_page_blocks b left join app_fields f on b.field_id=f.id  where b.report_id={$this->report_id} and parent_id=0 order by b.sort_order, b.id");
        while($block = db_fetch_array($block_query))
        {
            if($block['field_id']>0)
            {
                $name = $app_entities_cache[$block['field_entity_id']]['name'] . ': ' . \fields_types::get_option($block['field_type'], 'name', $block['field_name']);
            }
            else
            {
                $name = $block['name'];
            }
            
            $block_html .= '<li><a href="#" class="insert_block_to_description" data_insert="${' . $block['id'] . '}">${' . $block['id'] . '} ' . $name .  ' </a></li>';
        }
        
        $html = '
            <div class="dropdown">
                <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                  ' . TEXT_EXT_HTML_BLOCKS . '
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" style="max-height: 250px; overflow-y: auto">
                    ' . $block_html . '
                </ul>
            </div>
			
           '; 
       
       return $html;
    }            
}

