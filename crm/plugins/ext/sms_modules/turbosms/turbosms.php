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

class turbosms
{
	public $title;
	
	public $site;
        public $api;
        public $version;
        public $country;
	
	function __construct()
	{
		$this->title = TEXT_MODULE_TURBOSMS_TITLE;
		$this->site = 'https://turbosms.ua';
		$this->api = 'https://turbosms.ua/soap.html';
		$this->version = '1.0';
                $this->country = 'UA';
	}
	
	public function configuration()
	{
		$cfg = array();
		
		
		
		
		$cfg[] = array(
				'key'	=> 'connection_method',
				'type' => 'text',
				'default' => 'SOAP',
				'description' => (!extension_loaded('soap') ? '<span class="label label-danger">' . TEXT_MODULE_TURBOSMS_SOAP_ERROR . '</span>':''),				
				'title'	=> TEXT_MODULE_TURBOSMS_CONNECTION_METHOD,
				'params' =>array('class'=>'form-control input-large required'),
		);
		
		$cfg[] = array(
				'key'	=> 'login',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_TURBOSMS_LOGIN,				
				'params' =>array('class'=>'form-control input-large required'),				
		);
		
		$cfg[] = array(
				'key'	=> 'password',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_TURBOSMS_PASSWORD,				
				'params' =>array('class'=>'form-control input-large required'),
		);
					
		$cfg[] = array(
				'key'	=> 'sender',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_TURBOSMS_SENDER,				
				'params' =>array('class'=>'form-control input-large required'),				
		);
					
				
		return $cfg;
	}
		
	function send($module_id, $destination = array(),$text = '')
	{		
		global $alerts;
		
		if(!extension_loaded('soap')) return false;
		
		$cfg = modules::get_configuration($this->configuration(),$module_id);
		
		try 
		{
			$client = new SoapClient('http://turbosms.in.ua/api/wsdl.html');
			
			$auth = [
					'login' => $cfg['login'],
					'password' => $cfg['password']
			];
			
			$result = $client->Auth($auth);
						
			$sms = [
					'sender' => $cfg['sender'],
					'destination' => implode(',',$destination),
					'text' => $text
			];
			
			$result = $client->SendSMS($sms);
			
			if(strlen($result->SendSMSResult->ResultArray[0])==1)
			{
				$alerts->add($this->title . ' ' . TEXT_ERROR . ' ' . $result->SendSMSResult->ResultArray,'error');
			}
			else
			{
				//$alerts->add($result->SendSMSResult->ResultArray[0],'success');
			}								
			
		} 
		catch(Exception $e) 
		{
			$alerts->add($this->title . ' ' . TEXT_ERROR . ' ' . $e->getMessage(),'error');
		}					
	}
				
	
}