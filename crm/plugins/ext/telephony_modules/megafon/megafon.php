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

class megafon
{
    public $title;
    
    public $site;
    public $api;
    public $version;
    public $country;
    
    function __construct()
    {
        $this->title = TEXT_MODULE_MEGAFON_TITLE;
        $this->site = 'https://vats.megafon.ru';
        $this->api = 'https://help.megapbx.ru/rest_api';
        $this->version = '1.0';
        $this->country = 'RU';
    }
    
    public function configuration()
    {
        global $app_users_cache;
        
        $cfg = array();
        
                        
        $cfg[] = array(
            'key'	=> 'api_url',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_MODULE_MEGAFON_API_ADDRESS,
            'description'=>TEXT_MODULE_MEGAFON_API_ADDRESS_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
        
        $cfg[] = array(
            'key'	=> 'api_key',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_EXT_API_KEY,
            'description'=>TEXT_MODULE_MEGAFON_API_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
        
        $cfg[] = array(
            'key'	=> 'crm_url',
            'type' => 'text',            
            'default' => input_tag('crm_url',url_for_file('api/tel/megafon.php'),['class'=>'form-control select-all','readonly'=>'readonly']),
            'title'	=> TEXT_MODULE_MEGAFON_CRM_ADDRESS,                        
        );
        
        $cfg[] = array(
            'key'	=> 'crm_key',
            'type' => 'input',
            'default' => users::get_random_password(10,false),
            'title'	=> TEXT_MODULE_MEGAFON_CRM_KEY,
            'description'=>TEXT_MODULE_MEGAFON_CRM_INFO,
            'params' =>array('class'=>'form-control input-xlarge required select-all'),
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
            'description' => TEXT_MODULE_MEGAFON_EMAIL_INFO,
            'params' =>array('class'=>'form-control input-xlarge required chosen-select'),
        );
        
        $choices = [];
        
        $fields_query = fields::get_query(1," and f.type not in ('" . implode("','", array_merge(fields_types::get_reserved_data_types(), fields_types::get_users_types())) . "')");
        while($fields = db_fetch_array($fields_query))
        {
            $choices[$fields['id']] = $fields['name'];
        }
        
        $cfg[] = array(
            'key'	=> 'user_phone',
            'type' => 'dorpdown',
            'choices' => $choices,            
            'default' => '',
            'title'	=> TEXT_PHONE,
            'description' => TEXT_MODULE_MEGAFON_PHONE_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
                       
                
        return $cfg;
    }

    static function get_crm_key()    {                     
        $module_query = db_query("select id from app_ext_modules where module='megafon'");
        if($module = db_fetch_array($module_query))
        {
        
            $cfg_query = db_query("select * from app_ext_modules_cfg where modules_id='" . $module['id'] . "' and cfg_key='crm_key'");
            if($cfg = db_fetch_array($cfg_query))
            {
                return $cfg['cfg_value'];
            }
        }
		
        return '';        
    }    
	
    function prepare_url($module_id, $phone_number, $options)
    {
        global $alerts, $app_user, $is_js_inserted;
                     
        $cfg = modules::get_configuration($this->configuration(),$module_id);
                               
        if(strlen($cfg['users']) and in_array($app_user['id'], explode(',',$cfg['users'])))
        {            
            
            $url = parse_url($cfg['api_url']);
            
            if(has_access_to_call_history())
            {
                $call_history_url = '<a href="' . url_for('ext/call_history/view','search=' . preg_replace('/\D/', '',$phone_number) ) . '" target="_new"><i class="fa fa-history" aria-hidden="true"></i> История</a>';
            }
            else
            {
                $call_history_url = '';
            }
            
            $html = '
                <div class="btn-group">
					<a class="dropdown-toggle megafon-dropdown" type="button" data-toggle="dropdown" style="box-shadow:none; cursor: pointer">
					'  . $phone_number . '</i>
					</a>
					<ul class="dropdown-menu" role="menu" style="position: absolute;width: 175px;">
						<li>
                                                    ' . $call_history_url . '
						</li>
						<li>
							<a href="javascript: open_dialog(\'' . url_for('items/phone_call','path=' . $options['path'] . '&module_id=' . $module_id . '&field_id=' . $options['field']['id'] . '&item_id=' . $options['item']['id'] ) . '\')"><i class="fa fa-phone" aria-hidden="true"></i> Позвонить</a>
						</li>																				
					</ul>
				</div>
             ';
            
            if($is_js_inserted!=true)
            {
                $is_js_inserted = true;
                
                $html .= '
                    <script>
                        $(".megafon-dropdown").click(function (e) { //Default mouse Position 
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
            $url = parse_url($cfg['api_url']);
            
            $html = '
                <div class="btn-group">
                    <a class="dropdown-toggle megafon-dropdown1" type="button" data-toggle="dropdown" style="box-shadow:none; cursor: pointer">
                    '  . $phone_number . '</i>
                    </a>
                    <ul class="dropdown-menu" role="menu" >
                        <li>
                            <a href="javascript: open_dialog(\'' . url_for('ext/call_history/phone_call','module_id=' . $module_id . '&phone=' . $phone_number) . '\')"><i class="fa fa-phone" aria-hidden="true"></i> Позвонить ' . $phone_number . '</a>
                        </li>
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
        
        $phone_number = preg_replace('/\D/', '', $phone_number);
        
        $cfg = modules::get_configuration($this->configuration(),$module_id);
        
        if(strlen($cfg['users']) and in_array($app_user['id'], explode(',',$cfg['users'])))
        {
                                    
            $user = $app_user['fields']['field_' . $cfg['user_phone']]??'';
            
            if(strstr($user,'+'))
            {
                $user = preg_replace('/\D/', '', $user);
            }
            
            //print_rr($app_user);
                        
            $data = [
                'user' => $user,
                'token' => $cfg['api_key'],
                'cmd' => 'makeCall',
                'phone' => $phone_number,
            ];
            
            //echo $cfg['api_url'];
            //print_rr($data);
            //exit();
                                    
            $ch = curl_init($cfg['api_url']);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );  
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);	
            $result = curl_exec($ch);
            curl_close($ch);
            
            //print_rr($result);
            
            if(!strstr($result,'error'))
            {
                echo '
                    <div class="alert alert-success">' . TEXT_EXT_CALL_SENT . '</div>

                    <script>
                            setTimeout(function(){
                                    $("#ajax-modal").modal("toggle");
                            }, 3000);
                    </script>';
            }
            else
            {
                echo '<div class="alert alert-danger">'  . $this->title . ' ' . TEXT_ERROR . ' ' .  $result . '</div>';
            }
        }
    }

    
}