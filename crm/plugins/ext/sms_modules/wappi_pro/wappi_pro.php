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

class wappi_pro
{

    public $title;
    public $site;
    public $api;
    public $version;
    public $country;

    function __construct()
    {
        $this->title = TEXT_MODULE_WAPPI_PRO_TITLE;
        $this->site = 'https://wappi.pro';
        $this->api = 'https://wappi.pro/api-documentation';
        $this->version = '1.0';
        $this->country = 'RU';
    }

    public function configuration()
    {
        $cfg = array();

        $cfg[] = array(
            'key' => 'access_token',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_WAPPI_PRO_ACCESS_TOKEN,
            'description' => TEXT_MODULE_WAPPI_PRO_ACCESS_TOKEN_INFO,
            'params' => array('class' => 'form-control input-xlarge required'),
        );

        $cfg[] = array(
            'key' => 'instance_id',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_WAPPI_PRO_INSTANCE_ID,
            'description' => '',
            'params' => array('class' => 'form-control input-xlarge required'),
        );


        return $cfg;
    }

    function send($module_id, $destination = array(), $text = '')
    {
        global $alerts;

        $cfg = modules::get_configuration($this->configuration(), $module_id);

        foreach($destination as $phone)
        {
            $phone = preg_replace('/\D/', '', $phone);
            
            
            
            $message_json = json_encode([
                "recipient" => $phone,
                "body" => strip_tags($text, '<b><i><a><code><pre>')
            ]);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://wappi.pro/api/sync/message/send?profile_id=' . $cfg['instance_id']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $message_json);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $headers = array();
            $headers[] = 'Accept: application/json';
            $headers[] = 'Authorization: ' . $cfg['access_token'];
            $headers[] = 'Content-Type: text/plain';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            curl_close($ch);
                                    
            $result = json_decode($result, true);
            if(isset($result['status']) and $result['status']=='error' and is_object($alerts))
            {
                $alerts->add($this->title . ' ' . TEXT_ERROR  . ' ' . ($result['detail']??''), 'error');
            }  
            
            //print_rr($result);
            //exit();
        }
    }

}
