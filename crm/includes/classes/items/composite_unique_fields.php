<?php


class composite_unique_fields
{
    static function allowed_field_types()
    {
        return [
            'fieldtype_input',
            'fieldtype_input_masked',
            'fieldtype_input_dynamic_mask',                        
            'fieldtype_input_url',
            'fieldtype_input_ip',                        
            'fieldtype_input_email',
            'fieldtype_phone',
            'fieldtype_input_numeric',
            'fieldtype_input_date',
            'fieldtype_input_datetime',
            'fieldtype_input_date_extra',
            'fieldtype_dropdown',
            'fieldtype_dropdown_multiple',            
            'fieldtype_tags',
            'fieldtype_checkboxes',
            'fieldtype_radioboxes',                        
            'fieldtype_color',
            'fieldtype_users',
            'fieldtype_users_ajax',
            'fieldtype_entity',
            'fieldtype_entity_ajax',
            'fieldtype_entity_multilevel',
        ];
    }
    
    static function check($entities_id, $item_id = false, $parent_item_id=0)
    {
        global $app_fields_cache;
        
        $str = $_POST['form_data']??'';
        
        parse_str($str, $form_data);
        
        //print_r($form_data);
        //exit();
        
        if(!isset($form_data['fields']))
        {
            return json_encode(true);
        }
        
        if(isset($form_data['parent_item_id']) and $form_data['parent_item_id']>0)
        {
            $parent_item_id = $form_data['parent_item_id'];
        }
                        
        $form_fields = [];
        
        foreach($form_data['fields'] as $field_id=>$field_value)
        {                            
            $field_type = $app_fields_cache[$entities_id][$field_id]['type']??'';
                        
            switch ($field_type)
            {
                case 'fieldtype_input_date_extra':
                    $f = new fieldtype_input_date_extra();
                    $cfg = new settings($app_fields_cache[$entities_id][$field_id]['configuration']);
                    $field_value = $f->get_date_timestamp($field_value, $cfg);
                    break;
                case 'fieldtype_input_datetime':
                    $field_value = get_date_timestamp($field_value);
                    break;
                case 'fieldtype_input_date':
                    $field_value = get_date_timestamp($field_value);
                    break;
                case 'fieldtype_input_ip':
                    $field_value = ip2long($field_value);
                    break;
            }

            $form_fields[$field_id] = $field_value;
            
        }
        
        //print_r($form_fields);
        
        $messages = [];
        
        $rule_query = db_query("select * from app_composite_unique_fields where entities_id=" . $entities_id . " and is_active=1");
        while($rule = db_fetch_array($rule_query))
        {
            $f1 = $rule['field_1'];
            $f2 = $rule['field_2'];
                
            if(isset($form_fields[$f1]) and isset($form_fields[$f2]))
            {
                $field1 = $app_fields_cache[$entities_id][$f1];
                $field2 = $app_fields_cache[$entities_id][$f2];
                
                $f1_value = fields::get_value_by_type($entities_id, $f1, $form_fields[$f1]);
                $f2_value = fields::get_value_by_type($entities_id, $f2, $form_fields[$f2]);
                $f1_in = $f2_in =  '';
                                                
                $where_sql = [];
                if(in_array($field1['type'],choices_values::use_for_fieldtypes()))
                {
                    $f1_in = true;
                    $where_sql[] = "EXISTS(select id from app_entity_" . $entities_id . "_values as cv where cv.items_id=e.id and cv.fields_id='" . $f1  . "' and cv.value in (" . db_input_in($form_fields[$f1]) . ") limit 1)";                     
                }
                else
                {
                    $where_sql[] = "e.field_{$f1}='" . $form_fields[$f1] . "'";
                }
                
                if(in_array($field2['type'],choices_values::use_for_fieldtypes()))
                {
                    $f2_in = true;
                    $where_sql[] = "EXISTS(select id from app_entity_" . $entities_id . "_values as cv where cv.items_id=e.id and cv.fields_id='" . $f2  . "' and cv.value in (" . db_input_in($form_fields[$f2]) . ") limit 1)"; 
                }
                else
                {
                    $where_sql[] = "e.field_{$f2}='" . $form_fields[$f2] . "'";
                }
                
                //check parent
                if($rule['is_unique_for_parent'])
                {
                    $where_sql[] = "e.parent_item_id='" . $parent_item_id . "'";
                }
                                
                
                $check_query_sql = "select e.* from app_entity_{$entities_id} e where " . implode(" and ", $where_sql) . ($item_id ? " and id!=" . $item_id:"") . " limit 1";            
                //echo $check_query_sql;
                $check_query = db_query($check_query_sql);
                if($check = db_fetch_array($check_query))
                {
                    if($f1_in===true)
                    {
                        $v1 = strlen($check['field_' . $f1]) ? explode(',', $check['field_' . $f1]) : [];
                        $v2 = is_array($form_fields[$f1]) ? $form_fields[$f1] : [$form_fields[$f1]];
                        $intersect = array_intersect($v1, $v2);
                        $f1_in = fields::get_value_by_type($entities_id, $f1, $intersect);
                    }
                    
                    if($f2_in===true)
                    {
                        $v1 = strlen($check['field_' . $f2]) ? explode(',', $check['field_' . $f2]) : [];
                        $v2 = is_array($form_fields[$f2]) ? $form_fields[$f2] : [$form_fields[$f2]];                        
                        $intersect = array_intersect($v1, $v2);                        
                        $f2_in = fields::get_value_by_type($entities_id, $f2, $intersect);
                    }
                    
                    $messages[] = str_replace(['[f1]','[f2]','[f1_in]','[f2_in]'],[$f1_value, $f2_value, $f1_in,$f2_in],$rule['message']) . '<br>'; 
                }
            }
                        
        }
        
        if(count($messages))
        {
            return json_encode($messages); 
        }
        else
        {
            return json_encode(true);
        }
                
    }
    static function add_rule($entities_id, $item_id = false)
    {
        global $app_items_form_name, $public_form,$app_session_token, $app_path;
        
        $rule_query = db_query("select * from app_composite_unique_fields where entities_id=" . $entities_id . " and is_active=1");
        if(!$rule = db_fetch($rule_query))
        {
            return '';
        }

        if($app_items_form_name == 'registration_form')
        {
            $url = url_for("users/registration", "action=check_unique_rule&entities_id=1");
        }
        elseif($app_items_form_name == 'public_form')
        {
            $url = url_for("ext/public/form", "action=check_unique_rule&entities_id=" . $public_form["entities_id"] . "&id=" . $public_form['id']);
        }
        elseif($app_items_form_name == 'account_form')
        {
            $url = url_for("users/account", "action=check_unique_rule&entities_id=1");
        }
        else
        {
            $url = url_for("items/items", "action=check_unique_rule&path=" . $app_path . ($item_id ? "&id=" . $item_id : ""));
        }
        
        $html = '';
        
        $rule_query = db_query("select * from app_composite_unique_fields where entities_id=" . $entities_id . "  group by field_1");
        while($rule = db_fetch_array($rule_query))
        {
            $html .= '
                        "fields[' . $rule['field_1'] . ']": { 
                            remote:{
                                type: "POST",
                                url: "' . $url . '",
                                data: {                                    
                                    form_data: function(){   
                                        //return JSON.stringify($("#' . $app_items_form_name . '").serializeArray())   
                                        return $("#' . $app_items_form_name . '").serialize()    
                                    }
                                },
                                beforeSend: function(){
                                   //console.log($(this).serializeArray()) 
                                },
                                complete: function(data){
                                    //console.log(data)
                                }
                          }
                        },' . "\n";
        }
        
        return $html;
    }
}
