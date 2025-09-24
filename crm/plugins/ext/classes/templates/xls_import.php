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

class xls_import
{
    public $filename, $template_info, $entities_id, $count_new_items, $count_updated_items, $data;
    
    function __construct($filename, $template_info)
    {
        $this->filename = $filename;
        $this->template_info = $template_info;                
        $this->count_new_items = 0;
        $this->count_updated_items = 0;
        $this->worksheet = [];
    }
        
    function get_file_by_path()
    {
        $this->filename = 'xls_imort_' . time();
        
        if(strstr($this->template_info['filepath'],'http://') or strstr($this->template_info['filepath'],'https://'))
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->template_info['filepath']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $data = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if($code!='200')
            {
                die('Error ' . $code . ': can\'t open file ' . $this->template_info['filepath']);
            }
            
            
            file_put_contents(DIR_FS_TMP . $this->filename, $data);
        }
        else
        {
            file_put_contents(DIR_FS_TMP . $this->filename, file_get_contents($this->template_info['filepath']));
        }
    }     
            
    function unlink_import_file()
    {        
        if(is_file(DIR_FS_TMP . $this->filename))
        {           
            unlink(DIR_FS_TMP . $this->filename);
        }             
    }
    
    function get_text_delimiter()
    {        
        switch($this->template_info['text_delimiter'])
        {
            case 'tab':
                $text_delimiter = "\t";
                break;
            case 'space':
                $text_delimiter = ' ';
                break;
            default:
                $text_delimiter = $this->template_info['text_delimiter'];
                break;
        }
        
        return $text_delimiter;
    }
    
    function read_data()
    {                      
        $type = mime_content_type(DIR_FS_TMP . $this->filename);
        
        //echo $type;
        
        switch($type)
        {
            case 'text/plain':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();                                
                $reader->setInputEncoding($this->template_info['file_encoding']);
                $reader->setDelimiter($this->get_text_delimiter());
                $reader->setEnclosure('');
                $reader->setSheetIndex(0);

                $objPHPExcel = $reader->load(DIR_FS_TMP . $this->filename);                                        
                
                break;
            default:
                
                $objPHPExcel = PhpOffice\PhpSpreadsheet\IOFactory::load(DIR_FS_TMP . $this->filename);
                                                                
                break;
        }
        
        
        $objWorksheet = $objPHPExcel->getActiveSheet();

        $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
        $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5

        for($row = $this->template_info['start_import_line']; $row <= $highestRow; ++$row)
        {
            $is_empty_row = true;
            $worksheet_cols = array();

            for($col = 1; $col <= $highestColumnIndex; ++$col)
            {
                $value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                $value = is_null($value) ? '' : trim($value);
                $worksheet_cols[$col] = $value;

                if(strlen($value) > 0)
                    $is_empty_row = false;
            }

            if(!$is_empty_row)
            {
                $this->worksheet[] = $worksheet_cols;
            }
        }
    }
    
    function has_binded_fields()
    {
        $import_fields = json_decode($this->template_info['import_fields'],true);
        
        foreach($import_fields as $v)
        {
            if($v>0)
            {
                return true;
            }
        }
        
        return false;
    }
    
    function preview_data()
    {
        global $app_fields_cache;
        
        $this->read_data();
        
        //print_rr($this->worksheet);
        
        if(!count($this->worksheet))
        {
            return alert_warning(TEXT_NO_RECORDS_FOUND);
        }
                                
        
        if(!$this->has_binded_fields())
        {
            echo alert_warning(TEXT_EXT_NO_FIELDS_BIND_TO_COLUMNS);
        }
        
        $html = '<table class="table table-striped table-bordered table-hover">';
        
        //count cols
        $alphabet = range('A', 'Z');
        $count=1;
        $html .= '
                <tr>
                    <td style="background: #ebebeb"></td>';
        foreach($this->worksheet[0] as $k=>$row)
        {
            $html .= '<td style="background: #ebebeb; white-space:nowrap;">' . (isset($alphabet[$k-1]) ? $alphabet[$k-1] . ' ': '' ) . $count . '</td>';
            
            $count++;
        }
        $html .= '</tr>';
        
        //binded fields
        $entities_id = $this->template_info['entities_id'];
        $import_fields = json_decode($this->template_info['import_fields'],true);          
        $html .= '
                <tr>
                    <td style="background: #ebebeb"></td>';
        foreach($this->worksheet[0] as $col_num=>$row)
        {
            $field_id = isset($import_fields[$col_num-1]) ? $import_fields[$col_num-1] : 0;
            
            //skip field import if field ID not the uses Entity
            $field_name = isset($app_fields_cache[$entities_id][$field_id]) ? $app_fields_cache[$entities_id][$field_id]['name'] : '';
                                                  
            $html .= '<td style="background: #ebebeb;"><b>' . $field_name. '</b></td>';                        
        }
        $html .= '</tr>';
        
        
        //count rows
        $count = 1;
        foreach($this->worksheet as $row)
        {
            $html .= '
                <tr>
                    <td style="background: #ebebeb">' . $count . '</td>';
            
            foreach($row as $col)
            {
                $html .= '<td style="white-space: ' . (strlen($col)<=16 ? 'nowrap':'normal') . '">' . $col . '</td>';
            }
            
            $html .= '</tr>';
            
            $count++;
        }
        
        $html .= '</table>';
        
        return $html;
    }
    
    function import_data()
    {               
        $this->read_data();
        
        foreach($this->worksheet as $row)
        {
            $this->prepare_sql_data($row);
        }                           
    }
    
    function prepare_sql_data($row)
    {
        global $app_fields_cache, $app_entities_cache, $app_logged_users_id, $choices_names_to_id;
        
        $sql_data = [];
        $choices_values = array();
        $is_unique_item = true;
        $update_by_field_id = false;
        $update_by_field_value = '';
        
        $entities_id = $this->template_info['entities_id'];
        
        $unique_fields = fields::get_unique_fields_list($entities_id);
        
        $import_fields = json_decode($this->template_info['import_fields'],true);
        
        if(!$this->has_binded_fields())
        {
            die(TEXT_EXT_NO_FIELDS_BIND_TO_COLUMNS);
        }
        
        //print_rr($import_fields);
        //print_rr($row);
        
        foreach($row as $col_num=>$xls_field_value)
        {         
            $xls_field_value = trim($xls_field_value);
            
            $field_id = isset($import_fields[$col_num-1]) ? $import_fields[$col_num-1] : 0;
            
            //skip field import if field ID not the uses Entity
            if(!isset($app_fields_cache[$entities_id][$field_id])) continue;
            
            if($this->template_info['update_use_column']==$col_num-1)
            {
                $update_by_field_id = $field_id; 
                $update_by_field_value = $xls_field_value;
                
                //echo $update_by_field_id . ' = ' . $update_by_field_value;
            }
                                                
            $filed_info_query = db_query("select * from app_fields where id='" . db_input($field_id). "'");
            if($filed_info = db_fetch_array($filed_info_query))
            {                
                $cfg = new fields_types_cfg($filed_info['configuration']);
                
                switch($filed_info['type'])
                {
                    case 'fieldtype_entity':
                    case 'fieldtype_entity_ajax':
                    case 'fieldtype_entity_multilevel':
                        $values_list = array();                        
                        
                        if($heading_id = fields::get_heading_id($cfg->get('entity_id')))
                        {
                            $heading_field_info = db_find('app_fields',$heading_id);
                            if(in_array($heading_field_info['type'],['fieldtype_input','fieldtype_input_masked','fieldtype_text_pattern_static','fieldtype_input_url']))
                            {
                                
                                $item_query = db_query("select id from app_entity_" . $cfg->get('entity_id') . " where field_" . $heading_id . "='" . db_input($xls_field_value). "'");
                                if($item = db_fetch_array($item_query))
                                {
                                    $values_list[] =  $item['id'];
                                }
                                else
                                {
                                    if(($parent_entities_id = $app_entities_cache[$cfg->get('entity_id')]['parent_id'])>0)
                                    {
                                        $check_query = db_query("select id from app_entity_" . $cfg->get('entity_id'));
                                        if($check = db_fetch_array($check_query))
                                        {
                                            $parent_entities_item_id = $check['id'];
                                        }
                                    }
                                    else
                                    {
                                        $parent_entities_item_id = 0;
                                    }

                                    $item_sql_data = array();
                                    $item_sql_data['field_' . $heading_id] = trim($xls_field_value);
                                    $item_sql_data['date_added'] = time();
                                    $item_sql_data['created_by'] = $app_logged_users_id;
                                    $item_sql_data['parent_item_id'] = $parent_entities_item_id;

                                    db_perform('app_entity_' . $cfg->get('entity_id'),$item_sql_data);

                                    $item_id = db_insert_id();

                                    $values_list[] = $item_id;
                                }
                                
                                
                                //prepare choices values
                                $choices_values[$field_id] = $values_list;
                                
                                $sql_data['field_' . $field_id] = implode(',',$values_list);
                            }
                        }
                        break;
                    case 'fieldtype_dropdown':
                    case 'fieldtype_radioboxes':
                    case 'fieldtype_stages':
                        $value = $xls_field_value;
                        
                        if($cfg->get('use_global_list')>0)
                        {
                            if(isset($global_choices_names_to_id[$cfg->get('use_global_list')][$value]))
                            {
                                $sql_data['field_' . $field_id] = $global_choices_names_to_id[$cfg->get('use_global_list')][$value];
                            }
                            else
                            {
                                $fields_choices_info_query = db_query("select * from app_global_lists_choices where name='" . db_input($value) . "' and lists_id='" . db_input($cfg->get('use_global_list')) . "'");
                                if($fields_choices_info = db_fetch_array($fields_choices_info_query))
                                {
                                    $sql_data['field_' . $field_id] = $fields_choices_info['id'];
                                    
                                    $global_choices_names_to_id[$cfg->get('use_global_list')][$value] = $fields_choices_info['id'];
                                }
                                else
                                {
                                    $field_sql_data = array('lists_id'=>$cfg->get('use_global_list'),
                                        'parent_id'=>0,
                                        'name'=>$value);
                                    db_perform('app_global_lists_choices',$field_sql_data);
                                    
                                    $item_id = db_insert_id();
                                    
                                    $sql_data['field_' . $field_id] = $item_id;
                                    
                                    $global_choices_names_to_id[$cfg->get('use_global_list')][$value] = $item_id;
                                }
                            }
                        }
                        else
                        {
                            if(isset($choices_names_to_id[$field_id][$value]))
                            {
                                $sql_data['field_' . $field_id] = $choices_names_to_id[$field_id][$value];
                            }
                            else
                            {
                                $fields_choices_info_query = db_query("select * from app_fields_choices where name='" . db_input($value) . "' and fields_id='" . db_input($field_id) . "'");
                                if($fields_choices_info = db_fetch_array($fields_choices_info_query))
                                {
                                    $sql_data['field_' . $field_id] = $fields_choices_info['id'];
                                    
                                    $choices_names_to_id[$field_id][$value] = $fields_choices_info['id'];
                                }
                                else
                                {
                                    $field_sql_data = array('fields_id'=>$field_id,
                                        'parent_id'=>0,
                                        'name'=>$value);
                                    db_perform('app_fields_choices',$field_sql_data);
                                    
                                    $item_id = db_insert_id();
                                    
                                    $sql_data['field_' . $field_id] = $item_id;
                                    
                                    $choices_names_to_id[$field_id][$value] = $item_id;
                                }
                            }
                        }
                        
                        //prepare choices values
                        $choices_values[$field_id][] = $sql_data['field_' . $field_id];
                        
                        break;
                    case 'fieldtype_dropdown_multilevel':
                        $values_list = array();
                        $value = $xls_field_value;
                        
                        if(strlen($value))
                        {
                            $value_id = 0;
                            
                            if($cfg->get('use_global_list')>0)
                            {
                                if(isset($global_choices_names_to_id[$cfg->get('use_global_list')][$value]))
                                {
                                    $value_id = $global_choices_names_to_id[$cfg->get('use_global_list')][$value];
                                }
                                else
                                {
                                    $fields_choices_info_query = db_query("select * from app_global_lists_choices where name='" . db_input(trim($value)) . "' and lists_id='" . db_input($cfg->get('use_global_list')) . "'");
                                    if($fields_choices_info = db_fetch_array($fields_choices_info_query))
                                    {
                                        $value_id = $fields_choices_info['id'];
                                        $global_choices_names_to_id[$cfg->get('use_global_list')][$value] = $value_id;
                                    }
                                }
                            }
                            else
                            {
                                if(isset($choices_names_to_id[$field_id][$value]))
                                {
                                    $value_id = $choices_names_to_id[$field_id][$value];
                                }
                                else
                                {
                                    $fields_choices_info_query = db_query("select * from app_fields_choices where name='" . db_input(trim($value)) . "' and fields_id='" . db_input($field_id) . "'");
                                    if($fields_choices_info = db_fetch_array($fields_choices_info_query))
                                    {
                                        $value_id = $fields_choices_info['id'];
                                        $choices_names_to_id[$field_id][$value] = $value_id;
                                    }
                                }
                            }
                            
                            if($value_id>0)
                            {
                                if($cfg->get('use_global_list'))
                                {
                                    if(isset($global_choices_parents_to_id[$value_id]))
                                    {
                                        $value_array = $global_choices_parents_to_id[$value_id];
                                    }
                                    else
                                    {
                                        $value_array =  global_lists::get_paretn_ids($value_id);
                                        
                                        $global_choices_parents_to_id[$value_id] = $value_array;
                                    }
                                }
                                else
                                {
                                    if(isset($choices_parents_to_id[$field_id][$value_id]))
                                    {
                                        $value_array = $choices_parents_to_id[$field_id][$value_id];
                                    }
                                    else
                                    {
                                        $value_array = fields_choices::get_paretn_ids($value_id);
                                        
                                        $choices_parents_to_id[$field_id][$value_id] = $value_array;
                                    }
                                }
                                
                                $values_list = array_reverse($value_array);
                                
                                //prepare choices values
                                $choices_values[$field_id] = $values_list;
                                
                                $sql_data['field_' . $field_id] = implode(',',$values_list);
                            }
                        }
                        
                        break;
                    case 'fieldtype_grouped_users':
                    case 'fieldtype_dropdown_multiple':
                    case 'fieldtype_checkboxes':
                    case 'fieldtype_tags':
                        $values_list = array();
                        $value = explode(',', $xls_field_value);
                        
                        if($cfg->get('use_global_list')>0)
                        {
                            foreach($value as $value_name)
                            {
                                $fields_choices_info_query = db_query("select * from app_global_lists_choices where name='" . db_input(trim($value_name)) . "' and lists_id='" . db_input($cfg->get('use_global_list')) . "'");
                                if($fields_choices_info = db_fetch_array($fields_choices_info_query))
                                {
                                    $values_list[] = $fields_choices_info['id'];
                                }
                                else
                                {
                                    $field_sql_data = array('lists_id'=>$cfg->get('use_global_list'),
                                        'parent_id'=>0,
                                        'name'=>trim($value_name));
                                    db_perform('app_global_lists_choices',$field_sql_data);
                                    
                                    $item_id = db_insert_id();
                                    
                                    $values_list[] = $item_id;
                                }
                            }
                        }
                        else
                        {
                            foreach($value as $value_name)
                            {
                                $fields_choices_info_query = db_query("select * from app_fields_choices where name='" . db_input(trim($value_name)) . "' and fields_id='" . db_input($field_id) . "'");
                                if($fields_choices_info = db_fetch_array($fields_choices_info_query))
                                {
                                    $values_list[] = $fields_choices_info['id'];
                                }
                                else
                                {
                                    $field_sql_data = array('fields_id'=>$field_id,
                                        'parent_id'=>0,
                                        'name'=>trim($value_name));
                                    db_perform('app_fields_choices',$field_sql_data);
                                    
                                    $item_id = db_insert_id();
                                    
                                    $values_list[] = $item_id;
                                }
                            }
                        }
                        
                        //prepare choices values
                        $choices_values[$field_id] = $values_list;
                        
                        $sql_data['field_' . $field_id] = implode(',',$values_list);
                        
                        break;
                    case 'fieldtype_input_numeric':                        
                        $sql_data['field_' . $field_id] = str_replace([',',' '],['.',''],$xls_field_value);
                        break;
                    case 'fieldtype_input_date':
                    case 'fieldtype_input_date_extra':
                    case 'fieldtype_input_datetime':                            
                        $sql_data['field_' . $field_id] = (is_string($xls_field_value) ? strtotime($xls_field_value) :'');                       
                        break;
                    default:
                        $sql_data['field_' . $field_id] = $xls_field_value;
                        break;
                }
                
                //check uniques
                if(in_array($filed_info['id'],$unique_fields) and strlen($sql_data['field_' . $field_id]))
                {
                    $check_query = db_query("select id from app_entity_{$entities_id} where field_{$field_id}='" . db_input($sql_data['field_' . $field_id]) . "' limit 1");
                    if($check = db_fetch_array($check_query))
                    {
                        $is_unique_item = false;
                    }
                }
                
            }
        }
        
        
        //print_rr($sql_data);
        //exit();
        
        $item_id = false;
        $item_has_updated = false;
        
        if(($this->template_info['import_action']=='update' or $this->template_info['import_action']=='update_import') and $update_by_field_id and strlen($update_by_field_value))
        {
            $field_info = db_find('app_fields',$update_by_field_id);
                                               
            if($field_info['type']=='fieldtype_id')
            {
                $where_sql = " where id='" . db_input($update_by_field_value) . "'";
            }
            else
            {
                $where_sql = " where field_" . $field_info['id'] . "='" . db_input($update_by_field_value) . "'";
            }
            
            $where_sql .= " and parent_item_id = '" . $this->template_info['parent_item_id'] . "'";
            
            $item_query = db_query("select id from app_entity_" . $entities_id . $where_sql);
            if($item = db_fetch_array($item_query) and count($sql_data))
            {
                db_perform('app_entity_' . $entities_id,$sql_data,'update',"id=" . $item['id']);
                
                $item_has_updated = true;                                                
                
                $item_id = $item['id'];
                
                $this->count_updated_items++;
                                
            }
            
        }
                        
        //do insert
        if(!$item_has_updated and ($this->template_info['import_action']=='import' or $this->template_info['import_action']=='update_import'))
        {            
            //skip not unique items
            if($is_unique_item)
            {
                //set other values
                $sql_data['date_added'] = time();
                $sql_data['created_by'] = $app_logged_users_id;
                $sql_data['parent_item_id'] = (int)$this->template_info['parent_item_id'];
                
                
                //print_rr($sql_data);
                //exit();
                
                db_perform('app_entity_' . $entities_id,$sql_data);
                
                $item_id = db_insert_id();     
                
                $this->count_new_items++;                
            }
        }
        
        //insert choices values if exist
        if(count($choices_values)>0 and $item_id)
        {
            //reset current choices values if action is "update"
            if($this->template_info['import_action']!='import')
            {
                db_query("delete from app_entity_" . $entities_id . "_values where items_id = '" . $item_id . "' and fields_id='" . $field_id . "'");
            }
            
            foreach($choices_values as $field_id=>$values)
            {
                foreach($values as $value)
                {
                    db_query("INSERT INTO app_entity_" . $entities_id . "_values (items_id, fields_id, value) VALUES ('" . $item_id . "', '" . $field_id . "', '" . $value . "');");
                }
                
            }
        }
        
        //prepare item
        if($item_id)
        {
            //autoupdate all field types
            fields_types::update_items_fields($entities_id, $item_id);

            if(!$item_has_updated)
            {
                //run actions after item insert
                $processes = new processes($entities_id);
                $processes->run_after_insert($item_id);
            }
        }        
        
    }                
    
    
}