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


class yandexwallet
{
	public $title;
        public $site;
        public $api;
        public $version;
        public $js;
        public $country;
	
	function __construct()
	{
		$this->title = TEXT_MODULE_YANDEXWALLET_TITLE;
		$this->site = 'https://yoomoney.ru';
		$this->api = 'https://yoomoney.ru/docs/wallet';
		$this->version = '1.0';
                $this->country = 'RU';
	}
	
	public function configuration()
	{
		$cfg = array();
		
		$cfg[] = array(
				'key'	=> 'id',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_YANDEXWALLET_ID,
				'description' => TEXT_MODULE_YANDEXWALLET_INFO, 
				'params' =>array('class'=>'form-control input-large required'),				
		);
		
		$cfg[] = array(
				'key'	=> 'currency',
				'type' => 'text',
				'default' => 'RUB',
				'title'	=> TEXT_EXT_MODULE_TRANSACTION_CURRENCY,								
		);
					
		$cfg[] = array(
				'key'	=> 'custom_title',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_CUSTOM_TITLE,
				'description' => TEXT_DEFAULT . ' "' . $this->title. '".',
				'params' =>array('class'=>'form-control input-large')
		);
		
		$cfg[] = array(
				'key'	=> 'item_name',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_PURPOSE_OF_PAYMENT,
				'description' => TEXT_ENTER_TEXT_PATTERN_INFO,
				'params' =>array('class'=>'form-control input-large required'),				
		);
		
		$cfg[] = array(
				'key'	=> 'amount',
				'type' => 'input',
				'default' => '',
				'title'	=> TEXT_MODULE_PAYMENT_TOTAL,
				'description' => TEXT_MODULE_PAYMENT_TOTAL_INFO,
				'params' =>array('class'=>'form-control input-small required'),				
		);
				
		return $cfg;
	}
		
	function confirmation($module_id,$process_id)
	{
		global $app_path, $current_item_id, $current_entity_id, $app_redirect_to;
		
		$html = '';
		
		$cfg = modules::get_configuration($this->configuration(),$module_id);
						
		$item_info_query = db_query("select e.* " . fieldtype_formula::prepare_query_select($current_entity_id, '') . " from app_entity_" . $current_entity_id . " e  where e.id='" . $current_item_id . "'");
		if($item_info = db_fetch_array($item_info_query))
		{
			$amount = $item_info['field_' . $cfg['amount']];
			
			$fieldtype_text_pattern = new fieldtype_text_pattern();
			
			$item_name = $fieldtype_text_pattern->output_singe_text($cfg['item_name'],$current_entity_id,$item_info);
									
			$parameters = array();
			
			
			$parameters['quickpay-form'] = 'shop';
			$parameters['targets'] = $item_name;					
			$parameters['receiver'] = $cfg['id'];
			$parameters['sum'] = number_format($amount, 2,'.','');			
			$parameters['label'] = $current_item_id . '_' . $process_id;										
			$parameters['successURL'] = url_for('items/info','path=' . $app_path);
									
			$form_action_url = 'https://yoomoney.ru/quickpay/confirm.xml';
									
			$html .= '<form name="payment_confirmation" id="payment_confirmation" action="' . $form_action_url . '" method="post">';
			
			foreach($parameters as $k=>$v)
			{
				$html .= input_hidden_tag($k,$v) . "\n";
			}
			
			$html .= '<p class="to-pay">' . TEXT_EXT_TO_PAY . ': ' . $amount . ' RUB</p>';
			
			$choices = array();
			$choices['PC'] = TEXT_MODULE_YANDEXWALLET_PAYMENT_TYPE_WALLET;
			$choices['AC'] = TEXT_MODULE_YANDEXWALLET_PAYMENT_TYPE_CC;
			$html .= '
					<div class="row">
						<div class="col-md-4 margin-bottom-10">' . select_tag('paymentType',$choices,'',array('class'=>'form-control')) . '</div>
						<div class="col-md-3">' . submit_tag(TEXT_EXT_BUTTON_PAY,array('class'=>'btn btn-primary btn-pay')) . '</div>
					</div>
			</form>';
		}
		
		return $html;
	}
	
	function ipn($module_id)
	{
		$cfg = modules::get_configuration($this->configuration(),$module_id);
			
		if(isset($_POST['notification_type']) and isset($_POST['label']))
  	{				
  		$label_array = explode('_',$_POST['label']);
  		$current_item_id = $label_array[0];
  		$process_id = $label_array[1];
  		
			$process_info_query = db_query("select * from app_ext_processes where id='" . $process_id . "'");
			if($app_process_info = db_fetch_array($process_info_query))
			{
				$current_entity_id = $app_process_info['entities_id'];
																				
				$item_info_query = db_query("select e.* from app_entity_" . $current_entity_id . " e  where e.id='" . $current_item_id . "'");
				if($item_info = db_fetch_array($item_info_query))
				{																			
					$comment = '<b>' . TEXT_EXT_PAYMENT_NOTIFICATION . '</b><br>' .
							TEXT_EXT_PAYMENT_MODULE . ': ' . $this->title . '<br>' .
							TEXT_MODULE_PAYMENT_TOTAL . ': ' . number_format($_POST['amount'],2,'.','')  . ' RUB<br>' .							
							TEXT_MODULE_PAYMENT_STATUS . ': ' . modules::status_label(TEXT_MODULE_YANDEXWALLET_PAYMENT_COMPLATED,TEXT_MODULE_YANDEXWALLET_PAYMENT_COMPLATED);
																												
					$sql_data = array(
							'description' => $comment,
							'entities_id' => $current_entity_id,
							'items_id' => $current_item_id,
							'date_added' => time(),
							'created_by' => 0,							
					);
																		
					db_perform('app_comments',$sql_data);
										
					$processes = new processes($current_entity_id);
					$processes->items_id = $current_item_id;
					$processes->run($app_process_info, false, true);								
					
				}
			}
		}
	}
			
	
}