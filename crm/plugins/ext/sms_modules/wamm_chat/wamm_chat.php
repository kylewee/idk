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

class wamm_chat
{

    public $title;
    public $site;
    public $api;
    public $version;
    public $country;

    function __construct()
    {
        $this->title = TEXT_MODULE_WAMM_CHAT_TITLE;
        $this->site = 'https://wamm.chat';
        $this->api = 'https://wamm.chat/why/api';
        $this->version = '1.0';
        $this->country = 'RU';
    }

    public function configuration()
    {
        $cfg = array();

        $cfg[] = array(
            'key' => 'token',
            'type' => 'input',
            'default' => '',
            'title' => TEXT_MODULE_WAMM_CHAT_TOKEN,
            'description' => TEXT_MODULE_WAMM_CHAT_TOKEN_DESCRIPTION,
            'params' => array('class' => 'form-control input-large required'),
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
            
            $text = str_replace(["<br>","\n"],'%0A',$text);
            $text = strip_tags($text);
            
            $url = 'https://wamm.chat/api2/msg_to/' . $cfg['token'] . '/?phone=' . $phone . '&text=' . urlencode($text);
            
            //echo $url;
            //exit();
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $result = curl_exec($ch);
            curl_close($ch);

            if($result)
            {
                $result = json_decode($result, true);

                if($result['err']!=0)
                {
                    $alerts->add($this->title . ' ' . TEXT_ERROR . ' ' . $result['err'], 'error');
                }
            }
        }
    }

}
