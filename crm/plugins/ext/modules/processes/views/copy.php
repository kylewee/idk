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

<?php echo ajax_modal_template_header(TEXT_COPY) ?>

<?php echo form_tag('login', url_for('ext/processes/processes','action=copy&id=' . $_GET['id'])) ?>
<div class="modal-body">    
<?php
	$process_info = db_find('app_ext_processes',_get::int('id'));
	echo sprintf(TEXT_EXT_COPY_PROCESS_CONFIRMATION,$process_info['name'])
?>
</div> 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_COPY) ?>

</form>  