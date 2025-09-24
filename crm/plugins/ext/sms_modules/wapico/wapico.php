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

class wapico
{

    public $title;
    public $site;
    public $api;
    public $version;
    public $country;

    function __construct()
    {
        $this->title = TEXT_MODULE_WAPICO_TITLE;
        $this->site = 'https://wapico.ru';
        $this->api = 'https://wapico.ru/whatsapp-api-besplatno/';
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
            'title' => TEXT_MODULE_WAPICO_ACCESS_TOKEN,
            'description' => TEXT_MODULE_WAPICO_ACCESS_TOKEN_INFO,
            'params' => array('class' => 'form-control input-xlarge required'),
        );

        $cfg[] = array(
            'key' => 'instance_id',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_WAPICO_INSTANCE_ID,
            'description' => TEXT_MODULE_WAPICO_INSTANCE_ID_INFO,
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
            
            $params = [
                'number' => $phone,
                'type' => 'text',
                'message' => strip_tags($text, '<b><i><a><code><pre>'),
                'instance_id' => $cfg['instance_id'],
                'access_token' => $cfg['access_token'],
            ];
            
            //print_rr($params);
            //exit();

            $ch = curl_init("https://biz.wapico.ru/api/send.php");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $result = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($result, true);
            if(isset($result['error_code']) and is_object($alerts))
            {
                $alerts->add($this->title . ' ' . TEXT_ERROR . $result['error_code'] . ' ' . $result['description'], 'error');
            }  
            
            //print_rr($result);
            //exit();
        }
    }

}
