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


class sms_fly_ua
{
    public $title;
    public $site;
    public $api;
    public $version;
    public $country;

    function __construct()
    {
        $this->title = 'SMS-fly.ua';
        $this->site = 'https://sms-fly.ua';
        $this->api = 'https://sms-fly.ua/ru/gateway';
        $this->version = '1.0';
        $this->country = 'UA';
    }

    public function configuration()
    {
        $cfg = array();

        $cfg[] = array(
            'key' => 'api_key',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_EXT_API_KEY,
            'description' => TEXT_MODULE_SMS_FLY_UA_API_KEY_INFO,
            'params' => array('class' => 'form-control input-large required'),
        );
        
        $cfg[] = array(
            'key'   => 'sign',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_MODULE_SMS_FLY_UA_SOURCE,            
            'params' =>array('class'=>'form-control input-large'),				
        );
        

        return $cfg;
    }

    function send($module_id, $destination = array(), $text = '')
    {
        global $alerts;


        $cfg = modules::get_configuration($this->configuration(), $module_id);
        
        $api_key = $cfg['api_key'];        
        $url = 'https://sms-fly.ua/api/v2/api.php';

        foreach($destination as $phone)
        {
            
            $phone  = preg_replace('/\D/', '', $phone);
            
            $params = [
                'auth' => [
                    'key'=>$api_key
                    ],
                'action' => 'SENDMESSAGE',
                'data' => [
                    'recipient'=>$phone,
                    'channels' => ['sms'],
                    'sms' => [
                        'source'=> $cfg['sign'],
                        'ttl' => 300,
                        'text' => $text,
                    ],
                    
                ]
                
            ];
            
            //print_rr($params);
            //exit();
            
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $result = curl_exec($ch);
            curl_close($ch);

            if($result)
            {
                $result = json_decode($result, true);
                
                //print_rr($result);
                                                                
                if($result['success']!=1)
                {
                    $alerts->add($this->title . ' ' . TEXT_ERROR . ' ' . $result['error']['code'] . ' (' . $result['error']['description'] . ')', 'error');
                }                                                
            }
            
            //exit();
        }
    }

}
