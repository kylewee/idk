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


class moizvonki
{
    public $title;
    
    public $site;
    public $api;
    public $version;
    public $country;
    
    function __construct()
    {
        $this->title = TEXT_MODULE_MOIZVONKI_TITLE;
        $this->site = 'https://www.moizvonki.ru';
        $this->api = 'https://www.moizvonki.ru/guide/api';
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
            'title'	=> TEXT_MODULE_MOIZVONKI_API_ADDRESS,
            'description'=>TEXT_MODULE_MOIZVONKI_API_ADDRESS_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
        
        $cfg[] = array(
            'key'	=> 'api_key',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_EXT_API_KEY,
            'description'=>TEXT_MODULE_MOIZVONKI_API_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
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
            'description' => TEXT_MODULE_MOIZVONKI_EMAIL_INFO,
            'params' =>array('class'=>'form-control input-xlarge required chosen-select'),
        );
                       
                
        return $cfg;
    }
    
    function extra_actions($module_id)
    {
        global $app_user;
        
        $html = '<br>' . TEXT_EXT_CALL_HISTORY . ': ';
                   
        
        $cfg = modules::get_configuration($this->configuration(),$module_id);
        
        $data = [
            'user_name' => $app_user['email'],
            'api_key' => $cfg['api_key'],
            'action' => 'webhook.list',            
        ];
        
        $body = json_encode($data);
                
        
        $ch = curl_init($cfg['api_url']);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Content-Lengt:' . strlen($body)));            
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );            
        $result = curl_exec($ch);
        curl_close($ch);
        
        //echo $result;
        
        if(!strstr($result,'call.finish'))
        {
            $html  .= link_to(TEXT_TOGGLE_ON,url_for('ext/modules/moizvonki_ru','module_id=' . $module_id . '&action=webhook.subscribe'));
        }
        else
        {
            $html  .= link_to(TEXT_TOGGLE_OFF,url_for('ext/modules/moizvonki_ru','module_id=' . $module_id . '&action=webhook.unsubscribe'));
            
            $html  .= '<br>Завершение звонка: <code>' . $result .'</code>';
        }
        
        
        return $html;
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
                $call_history_url = '<a href="https://' . $url['host'] . '/calls/list/?date_to=&date_from=&direction=0&status=0&phone=' . preg_replace('/\D/', '', $phone_number) . '&duration=&contact=&order_by=start_time&order_type=desc" target="_new"><i class="fa fa-history" aria-hidden="true"></i> История</a>';
            }
            
            $html = '
                <div class="btn-group">
					<a class="dropdown-toggle moizvonki-dropdown" type="button" data-toggle="dropdown" style="box-shadow:none; cursor: pointer">
					'  . $phone_number . '</i>
					</a>
					<ul class="dropdown-menu" role="menu" style="position: absolute;width: 175px; height: 90px;">
						<li>
                                                    ' . $call_history_url . '
						</li>
						<li>
							<a title="' . TEXT_EXT_SMS . '" href="javascript: open_dialog(\'' . url_for('items/phone_sms','path=' . $options['path'] . '&module_id=' . $module_id . '&field_id=' . $options['field']['id'] . '&item_id=' . $options['item']['id']) . '\')"><i class="fa fa-commenting-o" aria-hidden="true"></i> Отправить СМС</a>
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
            $url = parse_url($cfg['api_url']);
            
            $html = '
                <div class="btn-group">
                    <a class="dropdown-toggle moizvonki-dropdown1" type="button" data-toggle="dropdown" style="box-shadow:none; cursor: pointer">
                    '  . $phone_number . '</i>
                    </a>
                    <ul class="dropdown-menu" role="menu" >
                        <li>
                            <a href="javascript: open_dialog(\'' . url_for('ext/call_history/phone_call','module_id=' . $module_id . '&phone=' . $phone_number) . '\')"><i class="fa fa-phone" aria-hidden="true"></i> Позвонить ' . $phone_number . '</a>
                        </li>
                        <li>
                            <a title="' . TEXT_EXT_SMS . '" href="javascript: open_dialog(\'' . url_for('ext/call_history/phone_sms','module_id=' . $module_id . '&phone=' . $phone_number) . '\')"><i class="fa fa-commenting-o" aria-hidden="true"></i> Отправить СМС</a>
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
            
            $ch = curl_init($cfg['api_url']);
                        
            $data = [
                'user_name' => $app_user['email'],
                'api_key' => $cfg['api_key'],
                'action' => 'calls.make_call',
                'to' => $phone_number,
            ];
            
            $body =  json_encode($data);
            
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Content-Lengt:' . strlen($body)));            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );            
            $result = curl_exec($ch);
            curl_close($ch);
            
            if($result=='Make call posted')
            {
                echo '
                    <div class="alert alert-success">' . TEXT_EXT_CALL_SENT . '</div>

                    <script>
						setTimeout(function(){
							$("#ajax-modal").modal("toggle");
						}, 1000);
					</script>';
            }
            else
            {
                echo '<div class="alert alert-danger">'  . $this->title . ' ' . TEXT_ERROR . ' ' .  $result . '</div>';
            }
        }
    }
    
    function sms_to_number($module_id, $phone_number, $message_text)
    {
        global $app_user, $alerts;
        
        $phone_number = preg_replace('/\D/', '', $phone_number);
        
        $cfg = modules::get_configuration($this->configuration(),$module_id);
        
        if(strlen($cfg['users']) and in_array($app_user['id'], explode(',',$cfg['users'])))
        {
            
            $ch = curl_init($cfg['api_url']);
            
            $data = [
                'user_name' => $app_user['email'],
                'api_key' => $cfg['api_key'],
                'action' => 'calls.send_sms',
                'to' => $phone_number,
                'text' => $message_text,
                
            ];
            
            $body =  json_encode($data);
            
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Content-Lengt:' . strlen($body)));
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            $result = curl_exec($ch);
            curl_close($ch);
            
            
            if($result=='SMS posted')
            {
                return true;
            }
            else
            {                
                $alerts->add($this->title . ' ' . TEXT_ERROR . ' ' . $result,'error');
                return false;
            }
        }
        
        return false;
    }
    

    
}