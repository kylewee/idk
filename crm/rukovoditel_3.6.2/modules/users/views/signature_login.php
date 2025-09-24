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

<h3 class="form-title"><?php echo TEXT_DIGITAL_SIGNATURE_LOGIN ?></h3>

<?php $module->select_certificate() ?>

 <div class="form-actions">
	<button type="button" id="back-btn" class="btn btn-default" onClick="location.href='<?php echo url_for('users/login')?>'"><i class="fa fa-arrow-circle-left"></i> <?php echo TEXT_BUTTON_BACK ?></button>	
</div>