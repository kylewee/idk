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
  case 'save':
      $cfg = new entities_cfg($_GET['entities_id']);
      
      if(!isset($_POST['cfg']['item_page_hidden_fields']))
      {
      	$_POST['cfg']['item_page_hidden_fields'] = '';
      }
                  
      foreach($_POST['cfg'] as $k=>$v)
      {                
        $cfg->set($k,$v);
      }
      
      $alerts->add(TEXT_CONFIGURATION_UPDATED,'success');
                      
      redirect_to('entities/item_page_configuration','entities_id=' . $_GET['entities_id']);
      
    break;
}

require(component_path('entities/check_entities_id'));

$cfg = new entities_cfg($_GET['entities_id']);

