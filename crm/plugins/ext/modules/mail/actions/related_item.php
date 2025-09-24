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

switch($app_module_action)
{  
  case 'remove_related_items';
      if(isset($_POST['items']))
      {      	      	
        foreach($_POST['items'] as $items_id)
        {        	
          db_query("delete from app_ext_mail_to_items where mail_groups_id='" . _get::int('mail_groups_id') . "' and entities_id='" . _get::int('entities_id') . "' and items_id='" . $items_id . "'");                    
        }
      }
      
      redirect_to('ext/mail/info','id=' . _get::int('mail_groups_id'));
    break;
    
  case 'add_related_item':
                
      if(isset($_POST['items']))
      {                      
        foreach($_POST['items'] as $items_id)
        {
          $check_query = db_query("select id from app_ext_mail_to_items where mail_groups_id='" . _get::int('mail_groups_id') . "' and entities_id='" . _get::int('entities_id') . "' and items_id='" . $items_id . "'");
          if(!$check = db_fetch_array($check_query))
          { 
          	$from_email = '';
          	$from_query = db_query("select from_email from app_ext_mail_groups_from where mail_groups_id='" . _get::int('mail_groups_id') . "'");
          	if($from = db_fetch_array($from_query))
          	{
          		$from_email = $from['from_email'];
          	}
          	
            $sql_data = [
							'mail_groups_id' => _get::int('mail_groups_id'),
							'entities_id' => _get::int('entities_id'),
							'items_id' => $items_id,
            	'from_email' => $from_email,
						];
						
						db_perform('app_ext_mail_to_items',$sql_data);
                        
          }
        }
      }
      
      redirect_to('ext/mail/info','id=' . _get::int('mail_groups_id'));
      
    break; 
}