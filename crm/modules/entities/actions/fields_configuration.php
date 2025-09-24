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

if(isset($_POST['field_type']))
{
  $field_type = new $_POST['field_type'];
  
  //echo '<h3 class="form-section">' . fields_types::get_tooltip($_POST['field_type']) . '</h3>';
  
  
  $tooltip = fields_types::get_tooltip($_POST['field_type']);
  
  if(strlen($tooltip))
  {
	  echo '
	    <div class="form-group">
	    	<label class="col-md-3 control-label">' . TEXT_INFO. '</label>
	      <div class="col-md-9"><p class="form-control-static">' .  $tooltip . '</p>
	      </div>			                                                                                                   
	    </div>
	  ';
  }
  
  if(method_exists($field_type,'get_configuration'))
  {  
    echo fields_types::render_configuration($field_type->get_configuration(array('entities_id'=>$_POST['entities_id'])),$_POST['id']);
  }
}

exit();