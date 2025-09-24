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

<?php echo ajax_modal_template_header(TEXT_DATABASE_EXPORT_APPLICATION) ?>

<div class="modal-body">
	<p><?php echo TEXT_DATABASE_EXPORT_EXPLANATION ?></p>
	
	<p><?php echo button_tag(TEXT_BUTTON_EXPORT_DATABASE,url_for('tools/db_backup','action=export_template'),false) ?></p>
	
	<?php echo tooltip_text(TEXT_DATABASE_EXPORT_TOOLTIP) ?>
</div>

<?php echo ajax_modal_template_footer('hide-save-button') ?>