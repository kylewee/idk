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


require('plugins/ext/sms_modules/twilio/twilio-php-master/src/Twilio/autoload.php');

use Twilio\Rest\Client;

class twilio
{
	public $title;
	
	public $site;
        public $api;
        public $version;
    
	
	function __construct()
	{
		$this->title = TEXT_MODULE_TWILIO_TITLE;
		$this->site = 'https://www.twilio.com';
		$this->api = 'https://www.twilio.com/docs/usage/api';
		$this->version = '1';
	}
	
	public function configuration()
	{
		$cfg = array();
		
					
		$cfg[] = array(
				'key'	=> 'sid',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_TWILIO_SID,				
				'params' =>array('class'=>'form-control input-large required'),				
		);
		
		$cfg[] = array(
				'key'	=> 'token',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_TWILIO_AUTH_TOKEN,
				'description' =>TEXT_MODULE_TWILIO_AUTH_TOKEN_INFO,
				'params' =>array('class'=>'form-control input-large required'),
		);
		
		$cfg[] = array(
				'key'	=> 'phone_number',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_PHONE,
				'description' =>TEXT_MODULE_TWILIO_PHONE_INFO,
				'params' =>array('class'=>'form-control input-large required'),
		);
												
				
		return $cfg;
	}
		
	function send($module_id, $destination = array(),$text = '')
	{		
		global $alerts;
						
		$cfg = modules::get_configuration($this->configuration(),$module_id);
				
		$client = new Client($cfg['sid'], $cfg['token']);
										
		foreach($destination as $phone)
		{
			$phone  = '+' . preg_replace('/\D/', '', $phone);
			
			try 
			{
				// Use the client to do fun stuff like send text messages!
				$response = $client->messages->create(
				    // the number you'd like to send the message to
				    $phone,
				    array(
				        // A Twilio phone number you purchased at twilio.com/console
				        'from' => $cfg['phone_number'],
				        // the body of the text message you'd like to send
				        'body' => $text
				    )
				);
			}
			catch (Exception $e) 
			{
				$alerts->add($this->title . ' ' . TEXT_ERROR . ' ' . $e->getMessage(),'error');				
			}							
		}					
	}				
	
}