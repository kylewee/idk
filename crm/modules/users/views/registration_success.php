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
?>

<h3 class="form-title"><?php echo (strlen(CFG_REGISTRATION_SUCCESS_PAGE_HEADING)>0 ? CFG_REGISTRATION_SUCCESS_PAGE_HEADING : TEXT_REGISTRATION_SUCCESS_PAGE_HEADING)?></h3>

<?php echo (strlen(CFG_REGISTRATION_SUCCESS_PAGE_DESCRIPTION)>0 ? '<p>' . CFG_REGISTRATION_SUCCESS_PAGE_DESCRIPTION . '</p>' : TEXT_REGISTRATION_SUCCESS_PAGE_DESCRIPTION) ?>

<?php 

$html = '
  <div class="modal-footer">    
    	<a href="' . url_for('users/login'). '" class="btn btn-default">' .  TEXT_BUTTON_CONTINUE . '</a>
  </div>';


echo $html;
?>