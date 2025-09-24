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


class email_notification_rules
{
    public $rule_info, $report_id, $count_items, $count_reports_filters;
    
    private $attachments;
    
    function __construct($rule_info = false)
    {
        if($rule_info)
        {
            $this->set_rule($rule_info);
        } 
                
    }
    
    function send()
    {
        global $app_user, $app_users_cache;
        
        $rules_query = db_query("select r.*, e.name as entity_name from app_ext_email_notification_rules r, app_entities e where e.id=r.entities_id and is_active=1 and find_in_set('" . date('H') ."',notification_time) and (find_in_set('" . date('N') . "',notification_days) or length(notification_days)=0) ",false);
        while($rules = db_fetch_array($rules_query))
        {
            //print_rr($rules);
            
            $app_user['id'] = 0;
            $app_user['group_id'] = 0;
            
            $this->attachments = [];
                    
            $this->set_rule($rules);
            
            foreach($this->get_send_to() as $user_id)
            {
                if(is_numeric($user_id))
                {
                    $app_user['id'] = $user_id;
                    $app_user['group_id'] = $app_users_cache[$user_id]['group_id'];
                    
                    //print_rr($app_user);
                }
                
                $body = $this->get_body();
                  
                //echo $user_id . ' - ' . $this->count_items . '<br>'; 
                //print_rr($app_user);
                
                if($this->count_items>0)
                {                             
                    $subject = str_replace(['${current_date}','${current_date_time}'],[format_date(time()), format_date_time(time())],$rules['subject']);
                    
                    users::send_to([$user_id], $subject, $body,$this->attachments);
                }
            }
        }
    }
    
    function get_send_to()
    {
        global $app_users_cache;
        
        switch($this->rule_info['action_type'])
        {
            case 'send_to_users':
                return explode(',',$this->rule_info['send_to_users']);
                break;
            case 'send_to_email':
                return preg_split('/\r\n|\r|\n/',$this->rule_info['send_to_email']);
                break;
            case 'send_to_user_group':
                $choices = [];
                foreach($app_users_cache as $user)
                {
                    foreach(explode(',',$this->rule_info['send_to_user_group']) as $group_id)
                    {
                        if(in_array($group_id,$user['group_array']))
                        {
                            $choices[] = $user['id'];
                        }
                    }
                }    
                
                //print_r($choices);
                
                return $choices;
                break;
        }
    }
    
    function set_rule($rule_info)
    {
        $this->rule_info = $rule_info;
        $this->report_id = default_filters::get_reports_id($this->rule_info['entities_id'], 'email_notification' . $this->rule_info['id']);
        $this->count_reports_filters = reports::count_filters_by_reports_id($this->report_id);
    }
    
    function get_body()
    {
        $html = '';
        
        if($this->count_reports_filters==0)
        {
            return alert_error(TEXT_NO_FILTERS_SETUP);
        }
        
        if(strstr($this->rule_info['description'],'${items}'))
        {
            $html = str_replace('${items}',$this->get_items_listing(),$this->rule_info['description']);
        }
        else
        {
            $html = $this->rule_info['description'] . $this->get_items_listing();
        }
        
        $html = str_replace(['${current_date}','${current_date_time}'],[format_date(time()), format_date_time(time())],$html);
        
        return $html;
    }
    
    function prepare_attachments($pattern, $item)
    {
        if(preg_match_all('/\[(\w+)\]/', $pattern, $matches))
        {
            foreach($matches[1] as $matches_key => $fields_id)
            {
                $fields_query = db_query("select id, type, configuration from app_fields where id='" . $fields_id . "' and type in ('" . implode("','", fields_types::get_attachments_types()) . "')");
                while ($fields = db_fetch_array($fields_query))
                {
                    
                    if (isset($item['field_' . $fields['id']]) and strlen($item['field_' . $fields['id']]))
                    {                        
                        foreach (explode(',', $item['field_' . $fields['id']]) as $filename)
                        {
                            $file = attachments::parse_filename($filename);

                            $this->attachments[$file['file_path']] = $file['name'];
                        }
                    }
                    
                }
            }
        }
    }
    
    function get_items_listing()
    {
        global $app_fields_cache;
        
        $html = '';
        
        $text_pattern = new fieldtype_text_pattern();
        
        switch($this->rule_info['listing_type'])
        {
            case 'list':
                
                foreach($this->get_items_list() as $item)
                {
                    $html .= '<div>' . $text_pattern->output_singe_text($this->rule_info['listing_html'], $this->rule_info['entities_id'], $item, ['is_email'=>true]).  '</div>';
                    
                    $this->prepare_attachments($this->rule_info['listing_html'], $item);
                }
                                
                break;
            case 'table':
                
                $html = '<table border="1" style="border-spacing: 0; border-collapse: collapse;" cellpadding="3" cellspacing="0">';
                
                //add thead
                $html .= '<thead><tr>';
                foreach(explode(',',$this->rule_info['listing_fields']) as $field_id)
                {
                    $field = $app_fields_cache[$this->rule_info['entities_id']][$field_id]??false;
                    if(!$field) continue;
                    
                    $html .= '<th>'  .  (strlen($field['short_name']??'') ? $field['short_name'] : $field['name']). '</th>';
                }
                $html .= '<tr></thead>';
                
                foreach($this->get_items_list() as $item)
                {
                    $html .= '<tr>';
                    
                    foreach(explode(',',$this->rule_info['listing_fields']) as $field_id)
                    {
                        $field = $app_fields_cache[$this->rule_info['entities_id']][$field_id]??false;
                        
                        if(!$field) continue;
                            
                        $html .= '<td>';
                                                                        
                        $output_options = array(
                            'class' => $field['type'],
                            'value' => items::prepare_field_value_by_type($field, $item),
                            'field' => $field,
                            'item' => $item,
                            'is_export' => true,
                            'is_print' => true,
                            'is_email' => true,
                            'hide_attachments_url' => true,
                            'path' => $field['entities_id'] . '-' . $item['id']);
                        
                        $value = fields_types::output($output_options);
                        
                        if($field['is_heading']==1)
                        {
                            $html .= '<a href="' . url_for('items/info','path=' . $field['entities_id'] . '-' . $item['id']) . '">' . $value . '</a>';
                        }
                        else
                        {
                            $html .= $value;
                        }
                        
                        
                        $html .= '</td>';
                    }
                    
                    $html .= '</tr>';
                    
                    $this->prepare_attachments('[' . str_replace(',','],[',$this->rule_info['listing_fields']). ']', $item);
                }
                $html .= '</table>';
                break;
        }
        
        return $html;
    }
    
    function get_items_list()
    {
        global $app_user;
        
        $items_list = [];
        
        if($this->count_reports_filters==0)
        {
            $this->count_items = 0;
            return [];
        }
                
        $query = new items_query($this->rule_info['entities_id'],[
            'add_formula' => true,
            'fields_in_query'=>  $this->rule_info['listing_type']=='list' ? $this->rule_info['listing_html'] : $this->rule_info['listing_fields'],
            'report_id' => $this->report_id,
            'add_filters' => true,
            'add_order' => true,
        ]);                        
        
        //debug query
        //echo 'Report:' . $this->report_id . '<br><code>' . $query->get_sql() . '</code><br>';
        
        $items_query = db_query($query->get_sql());
        $this->count_items = db_num_rows($items_query);
        while($items = db_fetch_array($items_query))
        {
            $items_list[] = $items;
        }
        
        return $items_list;
    }
    
    static function get_action_type_choices()
    {
        $choices = [
            'send_to_users' => TEXT_EXT_SEND_TO_USERS,
            'send_to_user_group' => TEXT_EXT_SEND_TO_USER_GROUP,
            'send_to_email' => TEXT_EXT_SEND_TO_EMAIL,
            
        ];
        
        return $choices;
    }
    
    static function get_action_type_name($type)
    {
        switch ($type)
        {
            case 'send_to_users':
                $text = TEXT_EXT_SEND_TO_USERS;
                break;
            case 'send_to_user_group':
                $text = TEXT_EXT_SEND_TO_USER_GROUP;
                break;
            case 'send_to_email':
                $text = TEXT_EXT_SEND_TO_EMAIL;
                break;            
            default:
                $text = '';
                break;
        }
        
        return $text;
    }
    
    static function delete_by_entity_id($entities_id)
    {
        db_query("delete from app_ext_email_notification_rules where entities_id='" . db_input($entities_id) . "'");
    }
}
