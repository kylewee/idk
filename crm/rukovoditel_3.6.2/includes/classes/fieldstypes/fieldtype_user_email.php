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

class fieldtype_user_email
{
  public $options;
  
  function __construct()
  {
    $this->options = array('name' => TEXT_FIELDTYPE_USER_EMAIL_TITLE,'title' => TEXT_FIELDTYPE_USER_EMAIL_TITLE);
  }
  
  function get_configuration()
  {
    $cfg = array();
    
    $cfg[] = array('title'=>TEXT_ALLOW_SEARCH, 'name'=>'allow_search','type'=>'checkbox','tooltip_icon'=>TEXT_ALLOW_SEARCH_TIP);
    
    return $cfg;
  }
  
  function render($field,$obj,$params = array())
  {
    return input_tag('fields[' . $field['id'] . ']',$obj['field_' . $field['id']],array('class'=>'form-control input-medium required email'));
  }
  
  function process($options)
  {               
    return db_prepare_input($options['value']);
  }
  
  function output($options)
  {
      
      if(isset($options['is_export']))
      {
          return $options['value'];
      }
      elseif(CFG_PUBLIC_REGISTRATION_USER_ACTIVATION=='email' and CFG_USE_PUBLIC_REGISTRATION==1 and $options['item']['is_email_verified']==0)
      {
          
          $html = '';
          $access_rules = new access_rules($options['field']['entities_id'], $options['item']);
          if(users::has_access('update',$access_rules->get_access_schema()))
          {
              $html = link_to_modalbox('<i id="user_email_verify_' . $options['item']['id'] . '" class="fa fa-refresh" aria-hidden="true"></i>', url_for('items/verify_email','path=1-' . $options['item']['id']),['title'=>TEXT_EMAIL_VERIFICATION_EMAIL_SUBJECT]);
          }
          
          return '<strike id="user_email_' . $options['item']['id'] . '" title="' . addslashes(TEXT_EMAIL_NOT_VERIFIED) . '">' . $options['value'] . '</strike> ' . $html;
      }
      else
      {
          return $options['value'];
      }
    
  }
}