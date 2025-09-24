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

class export_templates_xlsx
{
    protected $template;
    
    protected $expot_data;
    
    protected $items_list;
    
    protected $temp_filename;
    
    public $template_info;
    
    public $template_blocks;
    
    function __construct($template)
    {
        $this->template_info = $template;
        $this->template_blocks = new stdClass();
        $this->items_list = [];                
    }
    
    function prepare_template_file($entities_id, $items_id, $item = false)
    {     
        global $app_user;
        
        //check if template file exist
        if(!is_file(DIR_WS_TEMPLATES . $this->template_info['filename'])) die(TEXT_FILE_NOT_FOUND);
                        
        if(!$item)
        {
            $item_query = db_query("select e.*  " .  fieldtype_formula::prepare_query_select($entities_id, '') . " from app_entity_" . $entities_id . " e where e.id='" . $items_id . "'");
            $item = db_fetch_array($item_query);
        }
        
        $this->items_list[$entities_id] = $item;
        
        $parent_item_id = $item['parent_item_id'];
        
        foreach(entities::get_parents($entities_id) as $entity_id)
        {                                
            $parent_item_query = db_query("select e.*  " . fieldtype_formula::prepare_query_select($entity_id, '') . " from app_entity_" . $entity_id . " e where e.id='" . $parent_item_id . "'");
            $parent_item = db_fetch_array($parent_item_query);
            
            $this->items_list[$entity_id] = $parent_item;
            
            $parent_item_id = $parent_item['parent_item_id'];
        }                       
        
        //temp file
        $this->temp_filename = time() . '-' . $items_id . '-' . $app_user['id'] . '-' . $entities_id   . '-' . $this->template_info['id'] . '.xlsx';
        
        $this->prepare_template_blocks();
        
        $this->render_template();
        
        return $this->temp_filename;
    }
    
    protected function prepare_template_blocks()
    {
        global $app_fields_cache, $app_num2str, $app_entities_cache, $app_user;
        
        $this->template_blocks->current_date = format_date(time());
        $this->template_blocks->current_date_time = format_date_time(time());
        
        $blocks_query = db_query("select b.*, f.name, f.entities_id, f.type as field_type from app_ext_items_export_templates_blocks b, app_fields f, app_entities e where b.parent_id=0 and b.fields_id=f.id and b.templates_id = " . $this->template_info['id'] . " and f.entities_id=e.id order by e.sort_order, e.name, f.name");
        while($blocks = db_fetch_array($blocks_query))
        {  
            $block_settings = new settings($blocks['settings']);
            
            //for subentities
            if($blocks['field_type']=='fieldtype_id' and $app_entities_cache[$blocks['entities_id']]['parent_id']==$this->template_info['entities_id'])
            {
                $this->prepare_template_sub_entity_blocks($blocks['id'],$blocks['entities_id'],$block_settings);
                
                //skip other code;
                continue;
            }
            
            $item = $this->items_list[$blocks['entities_id']];
            $field = $app_fields_cache[$blocks['entities_id']][$blocks['fields_id']];                        
            $field_value = items::prepare_field_value_by_type($field, $item);
            
            
            $output_options = array(
                'class'=>$field['type'],
                'value'=>$field_value,
                'field'=>$field,
                'item'=>$item,
                'is_export'=>true,
                'is_print'=>true,
                'path'=>$blocks['entities_id']);
            
            //print_rr($output_options);
            
            $cfg = new fields_types_cfg($field['configuration']);
                                       
            $output_value_html = fields_types::output($output_options);
            $output_value = strip_tags($output_value_html); 
            $block_name = 'block_' . $blocks['id'];
            
            $output_value = $this->prepare_output_value($blocks,$field_value,$output_value);
            
            switch($blocks['field_type'])
            {             
                case 'fieldtype_related_records':
                    $this->prepare_template_related_records($blocks, $field, $item);
                    break;
                case 'fieldtype_created_by':
                case 'fieldtype_entity':
                case 'fieldtype_entity_ajax':
                case 'fieldtype_entity_multilevel':
                case 'fieldtype_users':
                case 'fieldtype_users_ajax':
                case 'fieldtype_users_approve':
                case 'fieldtype_user_roles':
                                        
                    $entity_id = (in_array($blocks['field_type'],['fieldtype_created_by','fieldtype_users','fieldtype_users_ajax','fieldtype_users_approve','fieldtype_user_roles']) ? 1 : $cfg->get('entity_id'));                    
                    
                    switch($block_settings->get('display_us'))
                    {
                        case 'inline':
                            $this->prepare_template_entity_inline_blocks($blocks,$entity_id,$field_value);                            
                            break;                        
                        case 'table':
                            $this->prepare_template_entity_table_blocks($blocks['id'],$entity_id,$field_value);
                            break;                                                    
                        default:
                            $this->template_blocks->$block_name = $output_value;
                            $this->prepare_template_entity_blocks($blocks['id'],$entity_id,$field_value);
                            break;
                    }
                    
                    break;    
                default:
                    $this->template_blocks->$block_name = $output_value;
                    break;
            }
        }
        
        
    }
    
    function prepare_template_related_records($blocks,$field, $item)
    {
        $block_id = $blocks['id'];
        $block_settings = new settings($blocks['settings']);
        $block_name = 'block_' . $block_id;
        
        $cfg = new fields_types_cfg($field['configuration']);        
        $entity_id = $cfg->get('entity_id');
        
        $related_records = new related_records($blocks['entities_id'],$item['id']);
        $related_records->set_related_field($field['id']);
        $related_items = $related_records->get_related_items();
              
        $output = [];
        
        if(count($related_items))
        {                        
            $query = new items_query($entity_id,[
                'add_formula'=>true,
                'report_id'=>  reports::get_reports_id_by_type($entity_id, 'templates_xlsx_block' . $block_id),
                'add_filters' => true,
                'add_order'=>true,
                'where' => "and e.id in (" . db_input_in($related_items) . ")",
            ]);
            
            $item_query = db_query($query->get_sql());                        
            while($item = db_fetch_array($item_query))
            {
                if($block_settings->get('display_us')=='table')
                {
                    $output[] = $item;
                }
                else
                {
                    if(strlen($block_settings->get('pattern')))
                    {
                        $text_pattern = new fieldtype_text_pattern();                    
                        $output[] = strip_tags($text_pattern->output_singe_text($block_settings->get('pattern'), $entity_id, $item));
                    }
                    else
                    {
                        $output[] = strip_tags(items::get_heading_field($entity_id, $item['id'],$item));
                    }
                }
            }
            
            if(count($output))
            {
                if($block_settings->get('display_us')=='table')
                {
                    $this->prepare_template_table_blocks($block_id, $entity_id, $output);
                }
                else
                {
                    $separator = (strlen($block_settings->get('separator')) ? $block_settings->get('separator') : '');
                
                    $this->template_blocks->$block_name = implode($separator,$output);
                }
            }
            else
            {
                $this->template_blocks->$block_name = '';
            }
        }
        
        //set emput value
        if(!count($output))
        {
            if($block_settings->get('display_us')=='table')
            {
                $this->prepare_template_empty_table_blocks($block_id);
            }
            else
            {
                $this->template_blocks->$block_name = '';
            }
        }                
    }
    
    function prepare_template_sub_entity_blocks($block_id, $entity_id, $block_settings)
    {
        global $sql_query_having;
        
        $fields_in_query = '';
        $blocks_query = db_query("select group_concat(b.fields_id) as block_fields  from app_ext_items_export_templates_blocks b where b.fields_id>0 and b.templates_id = " . $this->template_info['id'] . " and b.parent_id = " . $block_id,false);
        if($blocks = db_fetch_array($blocks_query))
        {
            $fields_in_query = $blocks['block_fields'];
        }
               
        $output = [];
        $items_id_list = [];
        
        $query = new items_query($entity_id,[
            'add_formula'=>true,
            'fields_in_query' => $fields_in_query,
            'report_id'=>  reports::get_reports_id_by_type($entity_id, 'templates_xlsx_block' . $block_id),
            'add_filters' => true,
            'add_order'=>true,
            'where' => "and e.parent_item_id='" . $this->items_list[$this->template_info['entities_id']]['id'] . "'",
        ]);
               
        $item_query = db_query($query->get_sql());           
        while($item = db_fetch_array($item_query))
        {
            $output[] = $item;
            $items_id_list[] = $item['id'];
        }
        
        switch($block_settings->get('display_us'))
        {
            case 'table':
                if(count($output))
                {
                    $this->prepare_template_table_blocks($block_id, $entity_id, $output);
                }
                else
                {
                    $this->prepare_template_empty_table_blocks($block_id);
                }
                break;
            case 'inline':                           
                $this->prepare_template_entity_inline_blocks(['id'=>$block_id,'settings'=>json_encode($block_settings->get_settings())],$entity_id,implode(',',$items_id_list));
                break;
        }
    }
    
    function prepare_template_entity_table_blocks($parent_block_id,$entity_id, $items_id_list)
    {
        $output = [];
        
        if(strlen($items_id_list))
        {
            $item_query = db_query("select e.*  " . fieldtype_formula::prepare_query_select($entity_id, '') . " from app_entity_" . $entity_id . " e where e.id in (" . db_input_in($items_id_list) . ")");
            while($item = db_fetch_array($item_query))
            {
                $output[] = $item;
            }
        }
        
        if(count($output))
        {
            $this->prepare_template_table_blocks($parent_block_id, $entity_id, $output);
        }
        else
        {
            $this->prepare_template_empty_table_blocks($parent_block_id);
        }
    }
    
    function prepare_template_empty_table_blocks($parent_block_id)
    {
        $block_name = 'block_' . $parent_block_id;
        $this->template_blocks->$block_name = [];
        
        $output_array = [];
        $blocks_query = db_query("select b.*,f.is_heading, f.name, f.entities_id, f.type as field_type,f.configuration as field_configuration from app_ext_items_export_templates_blocks b, app_fields f, app_entities e where  block_type='body_cell' and b.fields_id=f.id and b.templates_id = " . $this->template_info['id'] . " and b.parent_id = " . $parent_block_id . " and f.entities_id=e.id order by b.sort_order, b.id",false);
        while($blocks = db_fetch_array($blocks_query))
        {
            $output_array['block_' . $blocks['id']] = '';            
        }
        
        $output_array['num_' . $parent_block_id] = '';
        
        $this->template_blocks->$block_name[] = $output_array;
        
        $block_name = 'count_' . $parent_block_id;
        $this->template_blocks->$block_name = '';
        
        $block_name = 'text_' . $parent_block_id;
        $this->template_blocks->$block_name = '';
        
    }
    
    function prepare_template_table_blocks($parent_block_id,$entity_id, $output)
    {
        global $app_fields_cache, $app_num2str;
        
        $block_name = 'block_' . $parent_block_id;
        
        $this->template_blocks->$block_name = [];
                   
        foreach($output as $item_count=>$item)
        {      
            $output_array = [];
            $blocks_query = db_query("select b.*,f.is_heading, f.name, f.entities_id, f.type as field_type,f.configuration as field_configuration from app_ext_items_export_templates_blocks b, app_fields f, app_entities e where  block_type='body_cell' and b.fields_id=f.id and b.templates_id = " . $this->template_info['id'] . " and b.parent_id = " . $parent_block_id . " and f.entities_id=e.id order by b.sort_order, b.id",false);
            while($blocks = db_fetch_array($blocks_query))
            {
                $field = $app_fields_cache[$blocks['entities_id']][$blocks['fields_id']];
                $field_value = items::prepare_field_value_by_type($field, $item);
                
                $output_options = array(
                    'class'=>$field['type'],
                    'value'=>$field_value,
                    'field'=>$field,
                    'item'=>$item,
                    'is_export'=>true,                    
                    'path'=>$blocks['entities_id']);
                
                $output_value = strip_tags(fields_types::output($output_options));
                
                $output_value = $this->prepare_output_value($blocks,$field_value,$output_value);
                
                $output_array['block_' . $blocks['id']] = $output_value;                                                                                                                
            }
            
            $output_array['num_' . $parent_block_id] = ($item_count+1);
            
            $this->template_blocks->$block_name[] = $output_array;
        }
        
        //number of rows
        $number_of_rows = count($output);
        $number_of_rows_text = (isset($app_num2str->data[APP_LANGUAGE_SHORT_CODE]) ? $app_num2str->convert(APP_LANGUAGE_SHORT_CODE, $number_of_rows,false) : $app_num2str->convert('en', $number_of_rows,false));                
        
        $block_name = 'count_' . $parent_block_id;
        $this->template_blocks->$block_name = $number_of_rows;
        
        $block_name = 'text_' . $parent_block_id;
        $this->template_blocks->$block_name = $number_of_rows_text;
    }
    
    protected function prepare_output_value($blocks, $field_value,$output_value)
    {
        global $app_fields_cache, $app_num2str, $app_entities_cache, $app_user;
        
        $block_settings = new settings($blocks['settings']);
        
        switch($blocks['field_type'])
        {
            case 'fieldtype_date_added':
            case 'fieldtype_date_updated':
            case 'fieldtype_dynamic_date':
            case 'fieldtype_input_datetime':
            case 'fieldtype_input_date':
            case 'fieldtype_input_date_extra':    
                if(strlen($block_settings->get('date_format')) and $field_value>0)
                {                        
                    $output_value = format_date($field_value,$block_settings->get('date_format'));
                }               
                break;
            case 'fieldtype_input_numeric':
            case 'fieldtype_input_numeric_comments':
            case 'fieldtype_formula':
            case 'fieldtype_js_formula':
            case 'fieldtype_mysql_query':
            case 'fieldtype_ajax_request':   
                if(strlen($block_settings->get('number_in_words')))
                {
                    $number_in_words = $app_num2str->convert($block_settings->get('number_in_words'), $field_value,(strlen($block_settings->get('number_in_words')==2) ? false:true));
                    $output_value = $number_in_words;
                }
                else
                {
                    if(strlen($block_settings->get('number_format'))>0 and is_numeric($field_value))
                    {
                        $format = explode('/',str_replace('*','',$block_settings->get('number_format')));

                        $output_value = number_format($field_value,$format[0],$format[1],$format[2]);
                    }

                    $output_value = $block_settings->get('content_value_prefix') . $output_value . $block_settings->get('content_value_suffix');
                }
                break;
        }
        
        return $output_value;
        
    }
    
    function prepare_template_entity_blocks($parent_block_id,$entity_id, $item_id)
    {   
        global $app_fields_cache;
        
        $item = false; 
        
        if(isset($item_id) and strlen($item_id))
        {
            $item_query = db_query("select e.*  " . fieldtype_formula::prepare_query_select($entity_id, '') . " from app_entity_" . $entity_id . " e where e.id='" . $item_id . "'");
            $item = db_fetch_array($item_query);
        }
                
        
        $blocks_query = db_query("select b.*, f.name, f.entities_id, f.type as field_type from app_ext_items_export_templates_blocks b, app_fields f, app_entities e where b.fields_id=f.id and b.templates_id = " . $this->template_info['id'] . " and b.parent_id=" . $parent_block_id . " and f.entities_id=e.id order by e.sort_order, e.name, f.name");
        while($blocks = db_fetch_array($blocks_query))
        {
            $block_name = 'block_' . $blocks['id'];
            
            if($item)
            {                       
                $field = $app_fields_cache[$blocks['entities_id']][$blocks['fields_id']];
                $field_value = items::prepare_field_value_by_type($field, $item);
                                
                $output_options = array(
                    'class'=>$field['type'],
                    'value'=>$field_value,
                    'field'=>$field,
                    'item'=>$item,
                    'is_export'=>true,
                    'is_print'=>true,
                    'path'=>$blocks['entities_id']);
                
                $output_value = strip_tags(fields_types::output($output_options));
                
                $output_value = $this->prepare_output_value($blocks,$field_value,$output_value);
                                                
                $this->template_blocks->$block_name = $output_value;                
            } 
            else
            {                
                $this->template_blocks->$block_name = '';                
            }
        }                
    }    
    
    protected function prepare_template_entity_inline_blocks($blocks, $entity_id, $items_id_list)
    {
        $block_settings = new settings($blocks['settings']);
        $block_name = 'block_' . $blocks['id'];
        $block_id = $blocks['id'];
                        
        $output = [];                
                
        if(strlen($items_id_list))
        {            
            $query = new items_query($entity_id,[
                'add_formula'=>true,
                'report_id'=>  reports::get_reports_id_by_type($entity_id, 'templates_xlsx_block' . $block_id),
                'add_filters' => true,
                'add_order'=>true,
                'where' => "and e.id in (" . db_input_in($items_id_list) . ")",
            ]);
                                    
            $item_query = db_query($query->get_sql());
            while($item = db_fetch_array($item_query))
            {
                if(strlen($block_settings->get('pattern')))
                {
                    $text_pattern = new fieldtype_text_pattern();                    
                    $output[] = strip_tags($text_pattern->output_singe_text($block_settings->get('pattern'), $entity_id, $item));
                }
                else
                {
                    $output[] = strip_tags(items::get_heading_field($entity_id, $item['id'],$item));
                }
            }
        }
        
        if(count($output))
        {
            $separator = (strlen($block_settings->get('separator')) ? $block_settings->get('separator') : '');
        
            $this->template_blocks->$block_name = implode($separator,$output);                
        }
        else
        {
            $this->template_blocks->$block_name = '';
        }
    }
    
    protected function render_template()
    {
        //print_rr($this->template_blocks);
        
        $reader = PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load(DIR_WS_TEMPLATES . $this->template_info['filename']);
        $sheet_count = $spreadsheet->getSheetCount();
        
        for($i=0;$i<$sheet_count;$i++)
        {        
            $worksheet = $spreadsheet->getSheet($i);

            $re = new PhpStep\RenderWorksheet();
            $re->applyData($worksheet, $this->template_blocks);
        }

        $writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save(DIR_FS_TMP . $this->temp_filename);
    }
    
    function get_temp_filename()
    {
        return $this->temp_filename;
    }
    
    function download()
    {
        global $app_entities_cache;
        
        if(!is_file(DIR_FS_TMP . $this->temp_filename)) die(TEXT_FILE_NOT_FOUND);
        
        $filename = (strlen($_POST['filename']) ? $_POST['filename'] : $app_entities_cache[$this->template_info['entities_id']]['name']);
        
        // Redirect output to a client’s web browser (docx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . addslashes(app_remove_special_characters($filename)) . '.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        
        readfile(DIR_FS_TMP . $this->temp_filename);
        
        unlink(DIR_FS_TMP . $this->temp_filename);
        
        exit();
    }
    
    function dowload_archive($files,$zip_filename)
    {
        $zip = new ZipArchive();
        $zip_filename = app_remove_special_characters($zip_filename) . ".zip";
        $zip_filepath = DIR_FS_TMP . time()  . '-' . $zip_filename;
        
        
        //open zip archive
        $zip->open($zip_filepath, ZipArchive::CREATE | ZIPARCHIVE::OVERWRITE);
                                        
        //add files to archive
        foreach($files as $filename)
        {                           
            $zip->addFile(DIR_FS_TMP . $filename['filename'],$filename['name']);                          
        }
        
        $zip->close();
                        
        //check if zip archive created
        if (!is_file($zip_filepath))
        {
            exit("Error: cannot create zip archive in " . $zip_filepath );
        }
                
        //download file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$zip_filename);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zip_filepath));
        
        flush();
        
        readfile($zip_filepath);
        
        //delete temp zip archive file
        @unlink($zip_filepath);
        
        //delete temp files
        foreach($files as $filename)
        { 
            unlink(DIR_FS_TMP . $filename['filename']);
        }
    }    
    
    static function replace_blocks_id_in_filename($filename,$id_to_replace)
    {
        
        $template_blocks = new stdClass();
        foreach($id_to_replace as $block_id=>$new_block_id)
        {
            $block_name = 'block_' . $block_id;            
            $template_blocks->$block_name = '${block_' . $new_block_id . '}';
            
            $block_name = 'count_' . $block_id;            
            $template_blocks->$block_name = '${count_' . $new_block_id. '}';
                                    
            $block_name = 'text_' . $block_id;            
            $template_blocks->$block_name = '${text_' . $new_block_id. '}';
        }
                                
        
        $reader = PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load(DIR_WS_TEMPLATES . $filename);
        $sheet_count = $spreadsheet->getSheetCount();
        
        for($i=0;$i<$sheet_count;$i++)
        {        
            $worksheet = $spreadsheet->getSheet($i);

            $re = new PhpStep\RenderWorksheet();
            $re->applyData($worksheet, $template_blocks);
        }

        $writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save(DIR_WS_TEMPLATES . $filename);
        
    }
}
