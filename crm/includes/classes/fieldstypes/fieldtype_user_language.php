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

class fieldtype_user_language
{
  public $options;
  
  function __construct()
  {
    $this->options = array('name' => TEXT_FIELDTYPE_USER_LANGUAGE_TITLE,'title' => TEXT_FIELDTYPE_USER_LANGUAGE_TITLE);
  }
  
  function render($field,$obj,$params = array())
  {
    $selected  = (strlen($obj['field_' . $field['id']])>0 ? $obj['field_' . $field['id']] : CFG_APP_LANGUAGE);
    return select_tag('fields[' . $field['id'] . ']',app_get_languages_choices(),$selected,array('class'=>'form-control input-medium required'));
  }
  
  function process($options)
  {
      return db_prepare_input(str_replace(['..','/','\/'],'',$options['value']));
  }
  
  function output($options)
  {
    return implode(' ', array_map('ucfirst',explode('_',substr($options['value'],0,-4))));
  }
}