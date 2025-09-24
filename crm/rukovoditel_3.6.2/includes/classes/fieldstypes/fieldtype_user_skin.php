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

class fieldtype_user_skin
{
  public $options;
  
  function __construct()
  {
    $this->options = array('name' => TEXT_FIELDTYPE_USER_SKIN_TITLE,'title' => TEXT_FIELDTYPE_USER_SKIN_TITLE);
  }
  
  function render($field,$obj,$params = array())
  {
      if(!is_null(CFG_APP_SKIN) and strlen(CFG_APP_SKIN))
      {
          return '<p class="form-control-static">' . CFG_APP_SKIN . '</p>';
      }
      else
      {
          if(!strlen($obj['field_' . $field['id']]))
          {
              $obj['field_' . $field['id']] = 'default';
          }
          
          return select_tag('fields[' . $field['id'] . ']',app_get_skins_choices(false),$obj['field_' . $field['id']],array('class'=>'form-control input-medium'));
      }
  }
  
  function process($options)
  {
    return $options['value'];
  }
  
  function output($options)
  {
    return $options['value'];
  }
}