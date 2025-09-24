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

class blocks_html
{
    private $block, 
            $report,
            $entity_id, 
            $item_id, 
            $settings, 
            $field,
            $cfg,
            $item,
            $column_total;
    
    function __construct($block, $report)
    {
        $this->block = $block;
        $this->report = $report;
        
        $this->settings = new \settings($this->block['settings']);
        
        $this->entity_id = false; 
        $this->item_id = false;
        $this->item = [];
        $this->column_total = [];
        
        $this->field = false;
        
        //set field
        if($this->block['field_id']>0)
        {
            $this->set_field($this->block['field_id']);
        }
    }
    
    function set_field($field_id)
    {
        $field_query = db_query("select * from app_fields where id='" . (int)$field_id . "'");
        if($field = db_fetch_array($field_query))
        {
            $this->field  = $field;
            $this->cfg = new \settings($field['configuration']);
        }
    }
    
    function set_item($item)
    {
        $this->item = $item;                
    }
    
    function render()
    {
        $html = '';
        
        switch($this->block['block_type'])
        {
            case 'field':
                $html = $this->render_field();
                break;
            case 'nested_entity':
                $table = new blocks_nested_entity($this->block,$this->report,$this->item);
                $html = $table->render();
                break;
            case 'table':
                $table = new blocks_table($this->block,$this->report,$this->item);
                $html = $table->render();
                break;
            case 'php':
                $code = new blocks_php($this->block,$this->report,$this->item);
                $html = $code->render();
                break;            
            case 'html':
                $html = $this->settings->get('html_code');
                break;
            
        }
                        
        return $html;
    }
    
    function render_field()
    {         
        
        $value = \items::prepare_field_value_by_type($this->field, $this->item);
                                
        $output_options = array(
            'class' => $this->field['type'],
            'value' => $value,
            'field' => $this->field,
            'item' => $this->item,
            'is_export' => true,
            'is_print' => true,
            'path' => $this->field['entities_id'] . '-' . $this->item['id']);
        
        if(in_array($this->field['type'],['fieldtype_input_file','fieldtype_attachments']))
        {
            $output_options['is_export'] = false;
            $output_options['is_print'] = false;   
                        
        }
        
        $output_value = trim(\fields_types::output($output_options));    
                                                                       
        switch($this->field['type'])
        {
            case 'fieldtype_image':
            case 'fieldtype_image_ajax':    
            case 'fieldtype_user_photo':
                
                $output_value = str_replace('<img','<img class="report-page-img img-field-' . $this->field['id'] . '"' , $output_value); 
                
                $width = $this->settings->get('width');
                $height = $this->settings->get('height');
                
                if(strlen($width) or strlen($height))
                {
                    $output_value = preg_replace('/width=(\d+) /', '', $output_value);
                    $output_value = preg_replace('/height=(\d+) /', '', $output_value);
                    
                    $html = 'style="' . (strlen($width)>0 ? 'width:' . $width . ';': '') . (strlen($height)>0 ? 'height:' . $height : '') . '"';
                    $output_value = str_replace('<img','<img ' . $html, $output_value);
                }
                
                
                
                break;            
            case 'fieldtype_php_code':
            case 'fieldtype_items_by_query':
            case 'fieldtype_mysql_query':
            case 'fieldtype_formula':
            case 'fieldtype_todo_list':
            case 'fieldtype_users_approve':
            case 'fieldtype_signature':
            case 'fieldtype_textarea_wysiwyg':
            case 'fieldtype_barcode':
            case 'fieldtype_qrcode':
            case 'fieldtype_text_pattern':
            case 'fieldtype_text_pattern_static':
                $output_value = $output_value;                
                break;
            
            case 'fieldtype_date_added':
            case 'fieldtype_date_updated':
            case 'fieldtype_input_date':
            case 'fieldtype_input_date_extra':    
            case 'fieldtype_input_datetime':
            case 'fieldtype_dynamic_date':  
                $output_value = $this->render_field_date($output_value, $value);
                break;                                    
            case 'fieldtype_entity':
            case 'fieldtype_entity_ajax':
            case 'fieldtype_related_records':
            case 'fieldtype_users':
            case 'fieldtype_users_ajax':    
            case 'fieldtype_user_roles':
            case 'fieldtype_users_approve':
            case 'fieldtype_related_records':
                
                if($this->field['type']=='fieldtype_related_records')
                {
                    $value = $this->prepare_related_records_sql();
                }
                                                 
                switch($this->settings->get('display_us'))
                {
                    case 'inline':
                        $output_value = $this->render_field_entity_inline_list($value);
                        break;
                    case 'list':
                        $output_value = $this->render_field_entity_list($value);
                        break;
                    case 'table':
                        $output_value = $this->render_field_entity_table($value);
                        break;
                    case 'table_list':
                        break;
                    case 'tree_table':
                        break;
                }
                
                break;
            
            default:                
                if($this->report['type']=='print')
                {
                    $output_value = strip_tags($output_value);
                }                                
                break;
            
        }
        
        
        
        return $output_value;
    }
    
    function prepare_related_records_sql()
    {
        $table = \related_records::get_related_items_table_name($this->field['entities_id'],$this->cfg->get('entity_id'));
                        
        $sql = "select entity_{$this->cfg->get('entity_id')}{$table['sufix']}_items_id from {$table['table_name']} where entity_{$this->field['entities_id']}_items_id={$this->item['id']}";                
        
        return $sql;
    }
    
    function render_field_date($output_value, $value)
    {
        if(strlen($value) and $value>0 and strlen($this->settings->get('date_format')))
        {
            return date($this->settings->get('date_format'),$value);
        }
        else
        {
            return $output_value;
        }
    }
    
    function render_field_entity_inline_list($value)
    {
        if(!strlen($value))
        {
            return '';
        }
        elseif($this->field['type']!='fieldtype_related_records')
        {
            $value = db_input_in($value);
        }
        
        $field_entity_id = strlen($this->cfg->get('entity_id')) ? $this->cfg->get('entity_id') : 1;
                                
        $html = [];
        
        $query = new \items_query($field_entity_id, [
            'add_formula'   =>true,
            'fields_in_query' => $this->settings->get('pattern'),
            'report_id'     => $this->get_reports_id(),
            'add_filters'   => true,
            'add_order'     =>true,
            'where'         => " and id in (" . $value . ")",
        
        ]);
                
        $item_query = db_query($query->get_sql(),false);
        while($item = db_fetch_array($item_query))
        {
            if(strlen($this->settings->get('pattern')))
            {
                $pattern = new \fieldtype_text_pattern;
                $html[] = $pattern->output_singe_text($this->settings->get('pattern'), $field_entity_id, $item);
            }
            else
            {
                $html[] = \items::get_heading_field($field_entity_id, $item['id'], $item);
            }
        }  
        
        return implode(', ', $html);
    }
    
    function render_field_entity_list($value)
    {                
        if(!strlen($value))
        {
            return '';
        }
        elseif($this->field['type']!='fieldtype_related_records')
        {
            $value = db_input_in($value);
        }
        
        $field_entity_id = strlen($this->cfg->get('entity_id')) ? $this->cfg->get('entity_id') : 1;
                                
        $html = [];
        
        $query = new \items_query($field_entity_id, [
            'add_formula'   =>true,
            'fields_in_query' => $this->settings->get('pattern'),
            'report_id'     => $this->get_reports_id(),
            'add_filters'   => true,
            'add_order'     =>true,
            'where'         => " and id in (" . $value . ")",
        
        ]);
                
        $item_query = db_query($query->get_sql(),false);
        while($item = db_fetch_array($item_query))
        {
            if(strlen($this->settings->get('pattern')))
            {
                $pattern = new \fieldtype_text_pattern;
                $html[] = '<li>' . $pattern->output_singe_text($this->settings->get('pattern'), $field_entity_id, $item) . '</li>';
            }
            else
            {
                $html[] = '<li>' . \items::get_heading_field($field_entity_id, $item['id'], $item) . '</li>';
            }
        }  
        
        return '<ul ' . $this->settings->get('tag_ul_attributes') . '>' . implode('', $html) . '</ul>';
    }
    
    function render_field_entity_table($value)
    {                       
        if(!strlen($value))
        {
            return '';
        }
        elseif($this->field['type']!='fieldtype_related_records')
        {
            $value = db_input_in($value);
        }
                        
        $field_entity_id = strlen($this->cfg->get('entity_id')) ? $this->cfg->get('entity_id') : 1;
            
        $table_attributes = strlen($this->settings->get('tag_table_attributes')) ? $this->settings->get('tag_table_attributes') : 'class="table table-striped table-bordered table-hover"';
        
        $html = '
            <table ' . $table_attributes . '>
                <thead>';
        
        //extra rows
        $rows_query = db_query("select b.* from app_ext_report_page_blocks b where b.block_type='thead' and b.report_id = " . $this->report['id'] . " and b.parent_id = " . $this->block['id'] . " order by b.sort_order, b.id");
        while($rows = db_fetch_array($rows_query))
        {
            $blocks_query = db_query("select b.* from app_ext_report_page_blocks b where b.report_id = " . $this->report['id'] . " and b.parent_id = " . $rows['id'] . " order by b.sort_order, b.id");

            if(db_num_rows($blocks_query))
            {
                $html .= '<tr>';

                while($blocks = db_fetch_array($blocks_query))
                {
                    $settings = new \settings($blocks['settings']);

                    $cell_value = $settings->get('heading');                                               
                    $cell_settings = $settings->get('tag_td_attributes');

                    $html .= '<td ' . $cell_settings . '>' . $cell_value . '</td>';
                }

                $html .= '</tr>';
            }    
        }  
        
        //thead
        $html .= '<tr>';

        if($this->settings->get('line_numbering')==1)
        {    
            $html .= '<td class="line_numbering_heading">' . $this->settings->get('line_numbering_heading') . '</td>';
        }
        
        $fields_in_query = [];
        
        $blocks_query = db_query("select b.*,f.type as field_type, f.name as field_name, f.entities_id as field_entity_id from app_ext_report_page_blocks b left join app_fields f on b.field_id=f.id  where block_type='body_cell'  and b.parent_id = " . $this->block['id'] . " order by b.sort_order, b.id");
        while($blocks = db_fetch_array($blocks_query))
        {
            $settings = new \settings($blocks['settings']);

            $cell_settings = '';

            $cell_name = (strlen($settings->get('heading')) ? $settings->get('heading') : \fields_types::get_option($blocks['field_type'], 'name',$blocks['field_name']));

            $html .= '<td ' . $cell_settings . '>' . $cell_name . '</td>';
            
            if($blocks['field_id']>0)
            {
                $fields_in_query[] = $blocks['field_id'];
            }
        }
        $html .= '</tr>';

                
        //column numbering
        if($this->settings->get('column_numbering')==1)
        {
            $html .= '<tr class="column_numbering">';

            $count = 1;

            if($this->settings->get('line_numbering')==1)
            {
                $html .= '<td>' . $count . '</td>';
                $count++;
            }

            $blocks_query = db_query("select b.*,f.type as field_type, f.name as field_name, f.entities_id as field_entity_id from app_ext_report_page_blocks b left join app_fields f on b.field_id=f.id  where block_type='body_cell'  and b.parent_id = " . $this->block['id'] . " order by b.sort_order, b.id");
            while($blocks = db_fetch_array($blocks_query))
            {
                $settings = new \settings($blocks['settings']);
                
                $html .= '<td>' . $count . '</td>';
                $count++;
            }
            $html .= '</tr>';
        }
        
        $html .= '
            </thead>
            <tbody>    
        ';
        
                                             
        $item_count = 1;
        
        $query = new \items_query($field_entity_id, [
            'add_formula'   =>true,
            'fields_in_query' => $fields_in_query,
            'report_id'     => $this->get_reports_id(),
            'add_filters'   => true,
            'add_order'     =>true,
            'where'         => " and id in (" . $value . ")",
        
        ]);
                
        $item_query = db_query($query->get_sql(),false);
        while($item = db_fetch_array($item_query))
        {
            $html .= '<tr>';
            
            //line numbering count
            if($this->settings->get('line_numbering')==1)
            {
                $html .= '<td class="line_numbering">' . $item_count .  '</td>';
            }
            
            $blocks_query = db_query("select b.*,f.type as field_type, f.name as field_name, f.entities_id as field_entity_id from app_ext_report_page_blocks b left join app_fields f on b.field_id=f.id  where block_type='body_cell'  and b.parent_id = " . $this->block['id'] . " order by b.sort_order, b.id");
            while($blocks = db_fetch_array($blocks_query))
            {                                        
                $settings = new \settings($blocks['settings']);
                
                if(!isset($this->column_total['column_' . $blocks['id']]))
                {
                    $this->column_total['column_' . $blocks['id']] = 0;                    
                }
                
                switch($settings->get('value_type'))
                {
                    case 'field':
                        $value = $this->render_body_cell_field_value($item, $blocks);
                        break;
                    case 'php_code':
                        $value = $this->render_body_cell_php_value($item, $blocks);
                        break;
                    default:
                        $value = '';
                        break;
                }
                
                $html .= '<td ' . $settings->get('tag_td_attributes') . '>' . $value . '</td>';                                                               
            }
            
            $item_count++;
            
            $html .= '</tr>';                        
        }  
        
        $html .='
                </tbody>
                <tfoot>
        ';
        
        
        
        
        //extra rows
        $rows_query = db_query("select b.* from app_ext_report_page_blocks b where b.block_type='tfoot' and b.report_id = " . $this->report['id'] . " and b.parent_id = " . $this->block['id'] . " order by b.sort_order, b.id");
        while($rows = db_fetch_array($rows_query))
        {
            $blocks_query = db_query("select b.*,f.type as field_type, f.name as field_name, f.entities_id as field_entity_id  from app_ext_report_page_blocks b left join app_fields f on b.field_id=f.id where b.report_id = " . $this->report['id'] . " and b.parent_id = " . $rows['id'] . " order by b.sort_order, b.id");

            if(db_num_rows($blocks_query))
            {
                $html .= '<tr>';

                while($blocks = db_fetch_array($blocks_query))
                {
                    $settings = new \settings($blocks['settings']);

                    $cell_value = $settings->get('heading');                                               
                    $cell_settings = $settings->get('tag_td_attributes');
                    
                    switch($settings->get('value_type'))
                    {
                        case 'field':                            
                            $cell_value = $this->render_body_cell_field_value($this->item, $blocks);
                            break;
                        case 'php_code':                            
                            $cell_value = $this->render_body_cell_php_value($this->item, $blocks, $this->column_total);
                            break;                      
                    }

                    $html .= '<td ' . $cell_settings . '>' . $cell_value . '</td>';
                }

                $html .= '</tr>';
            }    
        } 
        
        $html .= '
                </tfoot>
            </table>';
        
        return $html;
    }
    
    function get_reports_id()
    {
        $reports_query = db_query("select id from app_reports where reports_type='report_page_block" . $this->block['id'] . "'");
        if($reports = db_fetch_array($reports_query))
        {
            return $reports['id'];
        }
        else
        {
            return 0;
        }
    }  
    
    function add_filters_query()
    {
        global $sql_query_having;
        
        $listing_sql_query = '';
        $reports_query = db_query("select id from app_reports where reports_type='report_page_block" . $this->block['id'] . "'");
        if($reports = db_fetch_array($reports_query))
        {                
            $listing_sql_query = \reports::add_filters_query($reports['id']);

            //prepare having query for formula fields
            /*if (isset($sql_query_having[$entities_id]))
            {
                $listing_sql_query .= \reports::prepare_filters_having_query($sql_query_having[$entities_id]);
            }*/
        }
        
        return $listing_sql_query;
    }
    
    function render_body_cell_field_value($item, $blocks)
    {
        global $app_fields_cache;
        
        $field = $app_fields_cache[$blocks['field_entity_id']][$blocks['field_id']];
        $field_value = \items::prepare_field_value_by_type($field, $item);
        
        if(is_numeric($field_value))
        {
            $this->column_total['column_' . $blocks['id']] += $field_value;            
        }

        $output_options = array(
            'class'=>$field['type'],
            'value'=>$field_value,
            'field'=>$field,
            'item'=>$item,
            'is_export'=>true,                    
            'path'=>$blocks['field_entity_id']);

        $output_value = strip_tags(\fields_types::output($output_options));

        $settings = new \settings($blocks['settings']);

        //apply number format
        if(strlen($settings->get('number_format'))>0 and is_numeric($output_value))
        {
            $format = explode('/',str_replace('*','',$settings->get('number_format')));

            $output_value = number_format($output_value,$format[0],$format[1],$format[2]);
        }

        //add sufix/prefix
        if(strlen($output_value))
        {
            $output_value = $settings->get('content_value_prefix') . $output_value . $settings->get('content_value_suffix');
        }
        
        //preapre some fields types
        switch($blocks['field_type'])
        {
            case 'fieldtype_date_added':
            case 'fieldtype_date_updated':
            case 'fieldtype_dynamic_date':            
            case 'fieldtype_input_datetime':
            case 'fieldtype_input_date':
            case 'fieldtype_input_date_extra':    
                if(strlen($settings->get('date_format')))
                {
                    $output_value = format_date($field_value,$settings->get('date_format'));
                }
                break;
        }
        
        return $output_value;
    }
    
    function render_body_cell_php_value($item, $blocks, $total = [])
    {
        global $app_entities_cache, $app_fields_cache, $app_user;
        
        $settings = new \settings($blocks['settings']);
        
        $php_code = $settings->get('php_code');
        
        try
        {                        
            eval($php_code);
        }
        catch (Error $e)
        {
            echo alert_error(TEXT_ERROR . ' ' . $e->getMessage() . ' on line ' . $e->getLine());
        }
        
        if(isset($output_value) and is_numeric($output_value))
        {
            $this->column_total['column_' . $blocks['id']] += $output_value;            
        }
        
        return (isset($output_value) ? $output_value : '');
    }
       
}
