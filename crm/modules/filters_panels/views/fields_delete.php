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

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	<h4 class="modal-title"><?php echo TEXT_HEADING_DELETE ?></h4>
</div>

<?php echo form_tag('delete', url_for('filters_panels/fields','action=delete&panels_id=' . $_GET['panels_id']. '&entities_id=' . $_GET['entities_id'] . '&id=' . $_GET['id']),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
     
<?php 	
	echo TEXT_ARE_YOU_SURE;
?>

  </div>
</div>
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>  