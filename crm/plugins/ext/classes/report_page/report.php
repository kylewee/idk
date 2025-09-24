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

class report
{
    private $report, $entity_id, $item_id, $item;
    
    function __construct($report)
    {
        $this->report = $report;
        
        $this->entity_id = false; 
        $this->item_id = false;
        $this->item = [];
    }
    
    function set_item($entity_id, $item_id)
    {
        $this->entity_id = $entity_id; 
        $this->item_id = $item_id;
        
        $parent_item_id = false;
        $entities = \entities::get_parents($this->entity_id,[$this->entity_id]);
        
        //print_rr($entities);
        
        foreach($entities as $entity_id)
        {
            $where_sql = $this->entity_id==$entity_id ? "e.id={$this->item_id}":"e.id={$parent_item_id}";
            
            $item_query = db_query("select e.*  " . \fieldtype_formula::prepare_query_select($entity_id) . " from app_entity_" . $entity_id . " e where {$where_sql}",false);
            if($item = db_fetch_array($item_query))
            {
                $parent_item_id = $item['parent_item_id'];
                
                if($this->entity_id!=$entity_id)
                {
                    foreach($item as $k=>$v)
                    {
                        if(!strstr($k,'field_'))
                        {
                            unset($item[$k]);
                        }
                    }
                }                                
                
                $this->item = array_merge($this->item,$item);
            }
        }
        
        //print_rr($this->item);
    }
    
    function get_html()
    {
        $html = $this->report['description'];                
        
        $block_query = db_query("select * from app_ext_report_page_blocks where report_id={$this->report['id']}");
        while($block = db_fetch_array($block_query))
        {
            $block_html = new blocks_html($block,$this->report);
            
            if($this->entity_id)
            {
                $block_html->set_item($this->item);
            }
                    
            $html = str_replace('${' . $block['id'] . '}',$block_html->render(),$html);
        }
        
        //add conditions
        $html = $this->apply_conditions($html);
        
        //add css
        if(strlen($this->report['css']))
        {
            $html .= '
                <style>
                    ' . $this->report['css'] . '
                </style>
                ';
        }
        
        return '<div class="report-page-body" id="report_page_' . $this->report['id'] . '">' . $html . '</div>';
    }
    
    function apply_conditions($html)
    {
        if(!strlen($html)) return $html;
        
        $item = $this->item;
                        
        //print_rr($item);
        
        if (preg_match_all('/({{if[^:]+:}})[^{{]+{{endif}}/', $html, $matches))
        {
            //print_rr($matches);
            
            foreach($matches[1] as $matches_key=>$condition)
            {   
                //prepare fields values in condition
                foreach($item as $k=>$v)
                {
                    if(strstr($k,'field_'))
                    {
                        $k = str_replace('field_','',$k);
                        $value = !is_numeric($v) ? "'" . $v .  "'" : $v;
                        $condition = str_replace('[' . $k . ']',$value,$condition);
                    }
                }
                
                //prepare condition php code
                $condition = str_replace(['{{if',':}}'],'',$condition);
                
                $condition = str_replace(array('&lt;', '&gt;','&#39;','&quot;'), array('<', '>',"'",'"'), $condition);
                
                $php_code = ' $condition = (' . $condition . ' ? true:false);';
                
                //echo $php_code;
                
                //eval code
                try
                {                        
                    eval($php_code);                    
                }
                catch (Error $e)
                {
                    echo alert_error(TEXT_ERROR . ' ' . $e->getMessage() . ' on line ' . $e->getLine() . '<br>' . $php_code);
                }
                
                //echo $condition;
                
                //remove code if condition return false
                if(!$condition)
                {
                    $html = str_replace($matches[0][$matches_key],'',$html); 
                }
                else
                {
                    //remove commands
                    $html = str_replace([$matches[1][$matches_key].'<br />',$matches[1][$matches_key]],'',$html); 
                }
               
            }
            
            //remove {{endif}} at the end to keep html blocks
            $html = str_replace(['{{endif}}<br />','{{endif}}'],'',$html); 
        }
        
        return $html;        
    }
    
    static function get_buttons_by_position($entities_id, $item_id, $position, $url_params = '')
    {
        global $app_user, $app_path;
                                      

        $reports_list = array();

        $html = '';

        $reports_query = db_query("select ep.* from app_ext_report_page ep, app_entities e where ep.is_active=1 and e.id=ep.entities_id and type='print' and find_in_set('" . str_replace('_dashboard', '', $position) . "',ep.button_position) and ep.entities_id='" . db_input($entities_id) . "' and (find_in_set(" . $app_user['group_id'] . ",users_groups) or find_in_set(" . $app_user['id'] . ",assigned_to)) order by ep.sort_order, ep.name",false);
        while ($reports = db_fetch_array($reports_query))
        {
            if (!in_array($position, ['menu_with_selected', 'menu_with_selected_dashboard']))
            {
                $items_filters = new \items_filters($entities_id, $item_id);
                if (!$items_filters->check(['report_type'=>'report_page' . $reports['id']]))
                {
                    continue;
                }
            }

            $button_title = (strlen($reports['button_title']) ? $reports['button_title'] : $reports['name']);
            $button_icon = (strlen($reports['button_icon']) ? $reports['button_icon'] : 'fa-print');

            $style = (strlen($reports['button_color']) ? 'color: ' . $reports['button_color'] : '');

            switch ($position)
            {
                case 'default':
                    $is_dialog = $reports['type']=='print' ? true : false;
                    $html .= '<li>' . button_tag($button_title, url_for('items/report_page', 'path=' . $app_path . '&report_id=' . $reports['id']), $is_dialog, array('class' => 'btn btn-primary btn-sm btn-report-page-' . $reports['id']), $button_icon) . '</li>';
                    $html .= app_button_color_css($reports['button_color'],'btn-report-page-' . $reports['id']);
                    break;
                case 'menu_more_actions':
                    $reports_list[] = array('id' => $reports['id'], 'name' => $button_title, 'entities_id' => $reports['entities_id'], 'button_icon' => $button_icon);
                    break;
                case 'menu_with_selected':
                    $reports_list[] = array('id' => $reports['id'], 'name' => $button_title, 'entities_id' => $reports['entities_id'], 'button_icon' => $button_icon);
                    break;
                case 'menu_print':
                    $html .= '<li>' . link_to_modalbox('<i class="fa ' . $button_icon . '"></i> ' . $button_title, url_for('items/report_page', 'path=' . $app_path . '&report_id=' . $reports['id']), ['style' => $style]) . '</li>';
                    break;
                case 'menu_with_selected_dashboard':
                    $html .= '<li>' . link_to_modalbox('<i class="fa ' . $button_icon . '"></i> ' . $button_title, url_for('items/print_template', 'templates_id=' . $reports['id'] . $url_params), ['style' => $style]) . '</li>';
                    break;
            }
        }



        switch ($position)
        {
            case 'default':
            case 'menu_with_selected_dashboard':
            case 'menu_print':
                return $html;
                break;
            case 'menu_more_actions':
            case 'menu_with_selected':
                return $reports_list;
                break;
        }
    }
    
    static function force_print_template()
    {
        global $app_force_print_template, $app_user;
        
        $force_print_template = explode('_',$app_force_print_template);
        $report_id = (int)str_replace('report','',$force_print_template[0]);
        $force_print_type = $force_print_template[1];
        $entity_id = (int)$force_print_template[2];
        $item_id = (int)$force_print_template[3];
        
        //reset
        $app_force_print_template = false;
        
        $html = '';
        $report_info_query = db_query("select * from app_ext_report_page where id='" . $report_id . "' and (find_in_set(" . $app_user['group_id'] . ",users_groups) or find_in_set(" . $app_user['id'] . ",assigned_to))");
        if($report_info = db_fetch_array($report_info_query))
        {
            $target = ($force_print_type=='printPopup') ? '_new' : '_self';
            $action = $force_print_type=='pdf' ? 'export_pdf' : '';
            
            if(strlen($report_info['save_filename']))
            {
                $item = \items::get_info($entity_id, $item_id);

                $pattern = new \fieldtype_text_pattern;
                $filename = $pattern->output_singe_text($report_info['save_filename'], $entity_id, $item);
            }
            else
            {
                $filename = $report_info['name'] . '_' . $entity_id;
            }
            
            $html = form_tag('print_template',url_for('items/report_page_print','path=' . $entity_id . '-' . $item_id . '&report_id=' . $report_info['id']),['target'=>$target]) .
                    input_hidden_tag('action',$action) . input_hidden_tag('filename',$filename) . '</form>';
            
            $html .= '
                <script>
                $(function(){
                    $("#print_template").submit();                    
                })
                </script>    
                ';
        }
        
        return $html;
    }
}
