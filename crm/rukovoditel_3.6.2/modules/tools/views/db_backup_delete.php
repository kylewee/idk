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

<?php echo ajax_modal_template_header(TEXT_WARNING) ?>

<?php echo form_tag('backup', url_for('tools/db_backup','action=delete&id=' . $_GET['id'])) ?>
<div class="modal-body">    
<?php
	$backup_info = db_find('app_backups',$_GET['id']);
	echo sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION, $backup_info['filename']);
?>
</div> 
<?php echo ajax_modal_template_footer(TEXT_DELETE) ?>

</form>  