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

<?php echo ajax_modal_template_header(TEXT_RESET_SORTING) ?>

<?php echo form_tag('choices_form', url_for('entities/fields_choices','action=sort_reset&entities_id=' . $_GET['entities_id'] . '&fields_id=' . $_GET['fields_id']),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
      <?php echo TEXT_VALUES_WILL_SORTED_BY_NAME ?>
  </div>
</div>

<?php echo ajax_modal_template_footer(TEXT_BUTTON_CONTINUE) ?>

</form> 