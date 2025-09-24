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


class moizvonki_sms
{

    public $title;
    public $site;
    public $api;
    public $version;
    public $country;

    function __construct()
    {
        $this->title = TEXT_MODULE_MOIZVONKI_SMS_TITLE;
        $this->site = 'https://www.moizvonki.ru';
        $this->api = 'https://www.moizvonki.ru/guide/api';
        $this->version = '1.0';
        $this->country = 'RU';
    }

    public function configuration()
    {
        $cfg = array();

        $cfg[] = array(
            'key'	=> 'api_url',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_MODULE_MOIZVONKI_SMS_API_ADDRESS,
            'description'=>TEXT_MODULE_MOIZVONKI_SMS_API_ADDRESS_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
        
        $cfg[] = array(
            'key'	=> 'api_key',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_EXT_API_KEY,
            'description'=>TEXT_MODULE_MOIZVONKI_SMS_API_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );
        
        $cfg[] = array(
            'key'	=> 'api_email',
            'type' => 'input',
            'default' => '',
            'title'	=> TEXT_EMAIL,
            'description'=>TEXT_MODULE_MOIZVONKI_SMS_EMAIL_INFO,
            'params' =>array('class'=>'form-control input-xlarge required'),
        );


        return $cfg;
    }

    function send($module_id, $destination = array(), $text = '')
    {
        global $alerts;

        $cfg = modules::get_configuration($this->configuration(), $module_id);        

        foreach($destination as $phone)
        {
            $phone  = preg_replace('/\D/', '', $phone);
            
            $ch = curl_init($cfg['api_url']);
            
            $data = [
                'user_name' => $cfg['api_email'],
                'api_key' => $cfg['api_key'],
                'action' => 'calls.send_sms',
                'to' => $phone,
                'text' => $text,
                
            ];
            
            //print_rr($data);
            //exit();
            
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
    }

}
