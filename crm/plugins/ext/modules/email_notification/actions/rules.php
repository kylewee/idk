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

switch ($app_module_action)
{
    case 'save':

        $sql_data = array(
            'entities_id' => $_POST['entities_id'],
            'action_type' => $_POST['action_type'],
            'send_to_users' => (isset($_POST['send_to_users']) ? implode(',', $_POST['send_to_users']) : ''),
            'send_to_user_group' => (isset($_POST['send_to_user_group']) ? implode(',', $_POST['send_to_user_group']) : ''),
            'send_to_email' => (isset($_POST['send_to_email']) ? $_POST['send_to_email'] : ''),            
            'subject' => $_POST['subject'],
            'description' => $_POST['description'],            
            'is_active' => (isset($_POST['is_active']) ? 1 : 0),            
            'notes' => $_POST['notes'], 
            'listing_type' => $_POST['listing_type'], 
            'listing_html' => $_POST['listing_html']??'', 
            'listing_fields' => isset($_POST['listing_fields']) ? implode(',',$_POST['listing_fields']) : '', 
            'notification_days' => isset($_POST['notification_days']) ? implode(',',$_POST['notification_days']) : '', 
            'notification_time' => isset($_POST['notification_time']) ? implode(',',$_POST['notification_time']) : '', 
        );

        if (isset($_GET['id']))
        {
            db_perform('app_ext_email_notification_rules', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_ext_email_notification_rules', $sql_data);
        }

        redirect_to('ext/email_notification/rules', 'entities_id=' . _get::int('entities_id'));

        break;
    case 'delete':

        if (isset($_GET['id']))
        {
            db_delete_row('app_ext_email_notification_rules', $_GET['id']);
            
            reports::delete_reports_by_type('email_notification' . $_GET['id']);
        }

        redirect_to('ext/email_notification/rules', 'entities_id=' . _get::int('entities_id'));
        break;
    
    case 'listing_sfg':
        
        $entities_id = _post::int('entities_id');

        $obj = array();

        if (isset($_POST['id']) and $_POST['id']>0)
        {
            $obj = db_find('app_ext_email_notification_rules', $_POST['id']);
        }
        else
        {
            $obj = db_show_columns('app_ext_email_notification_rules');            
        }
        
        $listing_type = $_POST['listing_type']??'';
        
        
        $html = '';
        switch($listing_type)
        {
            case 'list':
                $html .= '
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="cfg_sms_send_to_record_number">' . tooltip_icon(TEXT_ENTER_TEXT_PATTERN_INFO) . TEXT_PATTERN . fields::get_available_fields_helper($entities_id, 'listing_html', TEXT_AVAILABLE_FIELDS) . '</label>
                            <div class="col-md-9">	
                                  ' . textarea_tag('listing_html', $obj['listing_html'], array('class' => 'form-control code required')) . '                                  
                            </div>			
                         </div>        				
                        ';
                break;
            case 'table':
                $choices = [];
                
                $fields_query = fields::get_query($entities_id, "and f.type not in ('fieldtype_action')");
                while($fields = db_fetch_array($fields_query))
                {
                    $choices[$fields['id']] = fields_types::get_option($fields['type'], 'name', $fields['name']) . ' (#' . $fields['id']. ')';
                }
                
                $html .= '
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="cfg_sms_send_to_record_number">' . TEXT_FIELDS . '</label>
                            <div class="col-md-9">	
                                  ' . select_tag('listing_fields[]', $choices, $obj['listing_fields'], array('class' => 'form-control  required chosen-select chosen-sortable','chosen_order'=>$obj['listing_fields'],'multiple'=>'multiple')) . '                                  
                            </div>			
                         </div>        				
                        ';
                
                break;
        }
        
        echo $html;
        
        app_exit();
        
        exit();
        break;
    
    case 'get_entities_fields':

        $entities_id = _post::int('entities_id');

        $obj = array();

        if (isset($_POST['id']) and $_POST['id']>0)
        {
            $obj = db_find('app_ext_email_notification_rules', $_POST['id']);
        }
        else
        {
            $obj = db_show_columns('app_ext_email_notification_rules');            
        }


        $html = '';

        switch ($_POST['action_type'])
        {                           
            case 'send_to_users':
            
                $access_schema = users::get_entities_access_schema_by_groups($entities_id);

                $choices = array('' => '');

                $order_by_sql = (CFG_APP_DISPLAY_USER_NAME_ORDER == 'firstname_lastname' ? 'u.field_7, u.field_8' : 'u.field_8, u.field_7');
                $users_query = db_query("select u.*,a.name as group_name from app_entity_1 u left join app_access_groups a on a.id=u.field_6 where u.field_5=1 order by group_name, " . $order_by_sql);
                while ($users = db_fetch_array($users_query))
                {
                    if (!isset($access_schema[$users['field_6']]))
                    {
                        $access_schema[$users['field_6']] = array();
                    }

                    if ($users['field_6'] == 0 or in_array('view', $access_schema[$users['field_6']]) or in_array('view_assigned', $access_schema[$users['field_6']]))
                    {
                        $group_name = (strlen($users['group_name']??'') > 0 ? $users['group_name'] : TEXT_ADMINISTRATOR);
                        $choices[$group_name][$users['id']] = $app_users_cache[$users['id']]['name'];
                    }
                }


                $html .= '
                    <div class="form-group" style="margin-top: 30px;">
                        <label class="col-md-3 control-label" for="cfg_sms_send_to_record_number">' . tooltip_icon(TEXT_EXT_SEND_TO_USERS_INFO) . TEXT_EXT_SEND_TO_USERS . '</label>
                        <div class="col-md-9">	
                              ' . select_tag('send_to_users[]', $choices, $obj['send_to_users'], array('class' => 'form-control input-xlarge chosen-select required', 'multiple' => 'multiple')) . '                              
                        </div>			
                    </div>                                        
                    ';
                break;

            case 'send_to_email':            
                $html .= '
                        <div class="form-group" style="margin-top: 30px;">
                            <label class="col-md-3 control-label" for="cfg_sms_send_to_record_number">' . tooltip_icon(TEXT_EXT_SEND_TO_EMAIL_TIP) . TEXT_EMAIL . '</label>
                            <div class="col-md-9">
                                  ' . textarea_tag('send_to_email', $obj['send_to_email'], array('class' => 'form-control input-xlarge required')) . '							  	  
                            </div>
                        </div>
        				';
                break;
            case 'send_to_user_group':
                $choices = ['0' =>TEXT_ADMINISTRATOR];
                
                $group_query = db_query("select ag.* from app_access_groups ag, app_entities_access ea where ag.id=ea.access_groups_id and ea.entities_id={$entities_id} and length(ea.access_schema)>0");
                while($group = db_fetch_array($group_query))
                {
                    $choices[$group['id']] = $group['name'];
                }
                
                $html .= '
                    <div class="form-group" style="margin-top: 30px;">
                        <label class="col-md-3 control-label" for="cfg_sms_send_to_record_number">' . tooltip_icon(TEXT_EXT_SEND_TO_USERS_INFO) . TEXT_EXT_SEND_TO_USERS . '</label>
                        <div class="col-md-9">	
                              ' . select_tag('send_to_user_group[]', $choices, $obj['send_to_user_group'], array('class' => 'form-control input-xlarge chosen-select required', 'multiple' => 'multiple')) . '                              
                        </div>			
                    </div>                                        
                    ';
                break;
            
        }



        echo $html;

        exit();
        break;
}