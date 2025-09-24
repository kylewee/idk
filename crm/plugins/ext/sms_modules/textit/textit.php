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

class textit
{
    public $title;

    public $site;
    public $api;
    public $version;
    public $country;

    function __construct()
    {
        $this->title = 'Textit.biz | Sri Lanka\'s SMS Gateway';
        $this->site = 'http://textit.biz';
        $this->api = 'https://textit.biz/integration/';
        $this->version = '1.0';
        $this->country = 'LK';
    }

    public function configuration()
    {
            $cfg = array();
            
            $cfg[] = array(
                'key'	=> 'username',
                'type' => 'input',
                'default' => '',
                'title'	=> TEXT_USERNAME,
                'description' =>'Your Phone Number in international format',
                'params' =>array('class'=>'form-control input-large required'),
            );
            
            $cfg[] = array(
                'key'	=> 'password',
                'type' => 'input',
                'default' => '',
                'title'	=> TEXT_PASSWORD,
                'description' =>'4 Digit Password',
                'params' =>array('class'=>'form-control input-large required'),
            );
            
            return $cfg;
    }

    function send($module_id, $destination = array(),$text = '')
    {		
        global $alerts;

        $cfg = modules::get_configuration($this->configuration(),$module_id);
        
        $recipients = [];
        foreach ($destination as $phone)
        {
            $phone = preg_replace('/\D/', '', $phone);

            $params = [
                'id'=> $cfg['username'],
                'pw' => $cfg['password'],
                'to' => $phone,
                'text' => strip_tags($text),                
            ];
            
            $url = 'http://www.textit.biz/sendmsg/?' . http_build_query($params);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);            
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = explode(':',$result);
                        
            //print_rr($result);            
            //exit();
            
            if(trim($result[0])!='OK')
            {     
                $response_codes = [
                    'Ins_Crd'=>'Insufficient credits on the relevant balance.',
                    'to_invalid'=>'An invalid destination number or a blank value has been supplied.',
                    'Auth_Fail'=>'The account\'s credentials could not be verified.',
                    'Usr_Invalid'=>'The format of the supplied User ID is Invalid.',
                    'BeneficiaryID_Invalid'=>'Credit Beneficiary ID Invalid ',
                    'InvldCreditAmnt'=>'Invalid Credit amount',
                    'NotFound'=>'No record found in the query',                    
                ];
                
                $alerts->add($this->title . ': ' . implode(' ', $result)  . ', ' . ($response_codes[$result[1]] ?? ''),'error');
            } 
        }

    }
  
}