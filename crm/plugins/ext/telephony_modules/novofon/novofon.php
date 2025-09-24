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

include_once 'plugins/ext/telephony_modules/novofon/lib/vendor/autoload.php';

class novofon
{
    public $title;    
    public $site;
    public $api;
    public $version;
    public $country;
    
    function __construct()
    {
        $this->title = TEXT_MODULE_NOVOFON_TITLE;
        $this->site = 'https://novofon.com';
        $this->api = 'https://novofon.com/instructions/api';
        $this->version = '1.0';
        $this->country = 'RU';        
    }
    
    public function configuration()
    {
        global $app_users_cache;
        
        $cfg = array();
        
                        
        $cfg[] = array(
            'key'	=> 'api_key',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_MODULE_NOVOFON_API_KEY,
            'description'=>TEXT_MODULE_NOVOFON_API_KEY_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
        
        $cfg[] = array(
            'key'	=> 'api_secret',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_MODULE_NOVOFON_API_SECRET,
            'description'=>'',
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
        
        $cfg[] = array(
            'key'	=> 'crm_url',
            'type' => 'text',            
            'default' => input_tag('crm_url',url_for_file('api/tel/novofon.php'),['class'=>'form-control select-all','readonly'=>'readonly']) . tooltip_text(TEXT_MODULE_NOVOFON_CRM_ADDRESS_INFO),            
            'title'	=> TEXT_MODULE_NOVOFON_CRM_ADDRESS,                        
        );
        
        $choices = [];
        $choices[1] = TEXT_YES;
        $choices[0] = TEXT_NO;
        $cfg[] = array(
            'key'	=> 'is_sandbox',
            'type' => 'dorpdown',
            'choices' => $choices, 
            'default' => 0,
            'title'	=> TEXT_DEBUG_MODE,            
            'params' =>array('class'=>'form-control input-small required'),
        );
        
        $choices = array();
        $users_query = db_query("select u.*,a.name as group_name from app_entity_1 u left join app_access_groups a on a.id=u.field_6 order by u.field_8, u.field_7");
        while($users = db_fetch_array($users_query))
        {
            $group_name = ((isset($users['group_name']) and strlen($users['group_name'])>0) ? $users['group_name'] : TEXT_ADMINISTRATOR);
            $choices[$group_name][$users['id']] = $users['field_8'] . ' ' . $users['field_7'] . ' (' . $users['field_7'] . ')';
        }
        
        //print_rr($app_users_cache);
        
        $choices = array();
        foreach($app_users_cache as $id=>$user)
        {
            $choices[$user['group_name']][$id] = $user['name'] .' (' . $user['email']. ')';
        }
        
        $cfg[] = array(
            'key'	=> 'users',
            'type' => 'dorpdown',
            'choices' => $choices,
            'multiple' =>true,
            'default' => '',
            'title'	=> TEXT_USERS,
            'description' => TEXT_MODULE_NOVOFON_EMAIL_INFO,
            'params' =>array('class'=>'form-control input-xlarge required chosen-select'),
        );
        
        $choices = [''=>''];
        
        $fields_query = fields::get_query(1," and f.type in ('fieldtype_input','fieldtype_input_dynamic_mask','fieldtype_input_masked','fieldtype_phone')");
        while($fields = db_fetch_array($fields_query))
        {
            $choices[$fields['id']] = $fields['name'];
        }
                
        
        $cfg[] = array(
            'key'	=> 'pbx_number',
            'type' => 'dorpdown',
            'choices' => $choices,            
            'default' => '100',
            'title'	=> TEXT_MODULE_NOVOFON_PBX_NUMBER,
            'description' => TEXT_MODULE_NOVOFON_PBX_NUMBER_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
        
        
        $cfg[] = array(
            'key'	=> 'allow_sms',
            'type' => 'dorpdown',
            'choices' => [
                '1' => TEXT_YES,
                '0' => TEXT_NO
            ], 
            'default' => 0,
            'title'	=> TEXT_MODULE_NOVOFON_ALLOW_SMS,            
            'params' =>array('class'=>'form-control input-small'),
        );
        
        
        $cfg[] = array(
            'key'	=> 'celler_id',
            'type' => 'dorpdown',
            'choices' => $choices,            
            'default' => '',
            'title'	=> TEXT_MODULE_NOVOFON_CALLER_ID,
            'description' => TEXT_MODULE_NOVOFON_CALLER_ID_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
            'form_group' => ['form_display_rules' => 'cfg_allow_sms:1']
        );
                       
                
        return $cfg;
    }
    
    function extra_actions($module_id)
    {
        global $app_user;
        
        $cfg = modules::get_configuration($this->configuration(),$module_id);
        
        $html = '';
        
        $check_query = db_query("select * from app_ext_modules where id='" . $module_id . "' and is_active=1 order by sort_order");
        if(!$check = db_fetch_array($check_query))
        {
            return '';;
        }
        
        try{            
            $api = new \Novofon_API\Api($cfg['api_key'], $cfg['api_secret'], $cfg['is_sandbox']==1 ? true:false);
            $result = $api->getBalance();
            //print_rr($result);            
            $html = '<br>' . TEXT_MODULE_NOVOFON_BALANCE . ': ' . $result->balance . ' ' . $result->currency;  
        }
        catch(\Novofon_API\ApiException $e)
        {                
            $html =  alert_error($this->title . ' ' . TEXT_ERROR . ' (' . $e->getCode() . ') ' . $e->getMessage());
        } 
            
                
        return $html;
    }
    
    function prepare_url($module_id, $phone_number, $options)
    {
        global $alerts, $app_user, $is_js_inserted;
                     
        $cfg = modules::get_configuration($this->configuration(),$module_id);
                               
        if(strlen($cfg['users']) and in_array($app_user['id'], explode(',',$cfg['users'])))
        {            
              
            $call_history_url = '';
            if(has_access_to_call_history())
            {
                $call_history_url = '<li><a href="' . url_for('ext/call_history/view','search=' . preg_replace('/\D/', '',$phone_number) ) . '" target="_new"><i class="fa fa-history" aria-hidden="true"></i> История</a></li>';
            }
            
            $sms_url = '';
            $popup_height = 65;
            if($cfg['allow_sms']==1)
            {
                $sms_url = '
                    <li>
                        <a title="' . TEXT_EXT_SMS . '" href="javascript: open_dialog(\'' . url_for('items/phone_sms','path=' . $options['path'] . '&module_id=' . $module_id . '&field_id=' . $options['field']['id'] . '&item_id=' . $options['item']['id']) . '\')"><i class="fa fa-commenting-o" aria-hidden="true"></i> ' . TEXT_EXT_SEND_SMS . '</a>
                    </li>';
                
                $popup_height = 90;
            }
                                               
            $html = '
                <div class="btn-group">
					<a class="dropdown-toggle moizvonki-dropdown" type="button" data-toggle="dropdown" style="box-shadow:none; cursor: pointer">
					'  . $phone_number . '</i>
					</a>
					<ul class="dropdown-menu" role="menu" style="position: absolute;width: 175px; height: ' . $popup_height . 'px;">						
                                            ' . $call_history_url . '						
                                            ' . $sms_url . '
                                            <li>
                                                <a href="javascript: open_dialog(\'' . url_for('items/phone_call','path=' . $options['path'] . '&module_id=' . $module_id . '&field_id=' . $options['field']['id'] . '&item_id=' . $options['item']['id'] ) . '\')"><i class="fa fa-phone" aria-hidden="true"></i> ' . TEXT_EXT_CALL . '</a>
                                            </li>																				
					</ul>
				</div>
             ';
            
            if($is_js_inserted!=true)
            {
                $is_js_inserted = true;
                
                $html .= '
                    <script>
                        $(".moizvonki-dropdown").click(function (e) { //Default mouse Position 
                            //alert(e.pageX + " , " + e.pageY);
                            $(this).next().css("top",(e.pageY-$(window).scrollTop())+"px").css("left",e.pageX+"px")
                        });
                    </script>';
                
            }
            
            return $html;
        }
        else
        {
            return $phone_number;
        }
        
    }
    
    function call_history_url($module_id, $phone_number)
    {
        global $alerts, $app_user;
                     
        $cfg = modules::get_configuration($this->configuration(),$module_id);
                               
        if(strlen($cfg['users']) and in_array($app_user['id'], explode(',',$cfg['users'])))
        {                                                
            $sms_url = '';
            if($cfg['allow_sms']==1)
            {
                $sms_url = '
                    <li>
                        <a title="' . TEXT_EXT_SMS . '" href="javascript: open_dialog(\'' . url_for('ext/call_history/phone_sms','module_id=' . $module_id . '&phone=' . $phone_number) . '\')"><i class="fa fa-commenting-o" aria-hidden="true"></i> ' . TEXT_EXT_SEND_SMS . '</a>
                    </li>';
            }
            
            $html = '
                <div class="btn-group">
                    <a class="dropdown-toggle moizvonki-dropdown1" type="button" data-toggle="dropdown" style="box-shadow:none; cursor: pointer">
                    '  . $phone_number . '</i>
                    </a>
                    <ul class="dropdown-menu" role="menu" >
                        <li>
                            <a href="javascript: open_dialog(\'' . url_for('ext/call_history/phone_call','module_id=' . $module_id . '&phone=' . $phone_number) . '\')"><i class="fa fa-phone" aria-hidden="true"></i> ' . TEXT_EXT_CALL . ' ' . $phone_number . '</a>
                        </li>
                        ' . $sms_url . '
                        <li>
                            <a  href="javascript: copyTextToClipboard(\'' . $phone_number . '\')"><i class="fa fa-commenting-o" aria-hidden="true"></i> ' . TEXT_COPY. '</a>
                        </li>
                        <li>
                            <a href="' . url_for('ext/call_history/view','search=' . preg_replace('/\D/', '', $phone_number)) . '"><i class="fa fa-history" aria-hidden="true"></i> ' . TEXT_EXT_ALL_CALLS_BY_NUMBER . '</a>
                        </li>
                        
                        																				
                    </ul>
            </div>
             ';           
            
            return $html;
        }
        else
        {
            return $phone_number;
        }
        
    }
    
    function call_to_number($module_id, $phone_number)
    {
        global $app_user;
        
        $phone_number =  preg_replace('/\D/', '', $phone_number);
                        
        $cfg = modules::get_configuration($this->configuration(),$module_id);
        
        if(strlen($cfg['users']) and in_array($app_user['id'], explode(',',$cfg['users'])))
        {
            
            try{
                $api = new \Novofon_API\Api($cfg['api_key'], $cfg['api_secret'], $cfg['is_sandbox']==1 ? true:false);
                //internal number
                $pbx = $app_user['fields']['field_' . $cfg['pbx_number']]??'';

                $result = $api->requestCallback($pbx, $phone_number);
                                
                //print_rr($result);
                
                if($cfg['is_sandbox']==1)
                {
                    print_rr($result);
                }
                else
                {                
                    echo '
                       <div class="alert alert-success">' . TEXT_EXT_CALL_SENT . '</div>

                       <script>
                               setTimeout(function(){
                                       $("#ajax-modal").modal("toggle");
                               }, 3000);
                       </script>';
                }
            }
            catch(Exception $e)
            {
                echo alert_error($this->title . ' ' . TEXT_ERROR . ' (' . $e->getCode() . ') ' . $e->getMessage());
            }
                        
        }
    }
    
    function sms_to_number($module_id, $phone_number, $message_text)
    {
        global $app_user, $alerts;
        
        $phone_number =  preg_replace('/\D/', '', $phone_number);
        
        $cfg = modules::get_configuration($this->configuration(),$module_id);
        
        if(strlen($cfg['users']) and in_array($app_user['id'], explode(',',$cfg['users'])))
        {
            $sourceNumber = $app_user['fields']['field_' . $cfg['celler_id']]??'';
            $sourceNumber = preg_replace('/\D/', '', $sourceNumber);
            
            try{
                $api = new \Novofon_API\Api($cfg['api_key'], $cfg['api_secret'], $cfg['is_sandbox']==1 ? true:false);
                $result = $api->sendSms($phone_number, $message_text, $sourceNumber);
                
                //print_rr($result);
                
                if($cfg['is_sandbox']==1)
                {
                    print_rr($result);
                }
                else
                {
                  return true;   
                }                
            }
            catch(Exception $e)
            {                
                echo alert_error($this->title . ' ' . TEXT_ERROR . ' (' . $e->getCode() . ') ' . $e->getMessage());
            }                                               
        }
        
        return false;
    }
    
    public function play_audio_file($recording)
    {
        $module_id = modules::get_id_my_name('novofon');
        $cfg = modules::get_configuration($this->configuration(),$module_id);
        
        $html = '';
        try{
            $api = new \Novofon_API\Api($cfg['api_key'], $cfg['api_secret'], $cfg['is_sandbox']==1 ? true:false);
            $result = $api->getPbxRecord($recording,null);
            
            if($cfg['is_sandbox']==1)
            {
                print_rr($result);
            }
            
            if(isset($result->link))
            {
                $html = '<a href="' . $result->link . '" class="btn btn-default"><i class="fa fa-play-circle-o" aria-hidden="true"></i> ' . TEXT_PLAY_AUDIO_FILE . '</a>';
            }
        }
        catch(Exception $e)
        {                
            echo alert_error($this->title . ' ' . TEXT_ERROR . ' (' . $e->getCode() . ') ' . $e->getMessage());
        } 
        
        return $html;
    }

    
}