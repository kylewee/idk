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

<?php echo ajax_modal_template_header(TEXT_SORT_VALUES) ?>

<?php echo form_tag('choices_form', url_for('filters_panels/fields','action=sort&entities_id=' . $_GET['entities_id'] . '&panels_id=' . $_GET['panels_id']),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
 
<div class="dd" id="choices_sort">
<ol class="dd-list">      
<?php 
$fields_query = db_query("select pf.*, f.name as field_name, f.type as field_type from app_filters_panels_fields pf, app_fields f where pf.fields_id=f.id and pf.panels_id='" . _get::int('panels_id') . "' order by pf.sort_order");

if(db_num_rows($fields_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>';

while($fields = db_fetch_array($fields_query))
{
	echo '<li class="dd-item" data-id="' . $fields['id'] . '"><div class="dd-handle">' . fields_types::get_option($fields['field_type'],'name',$fields['field_name']) . '</div></li>';
}
?>
</ol>
</div>
      
   </div>
</div> 
<?php echo input_hidden_tag('choices_sorted') ?> 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
$(function(){
  $('#choices_sort').nestable({
      group: 1
  }).on('change',function(e){
    output = $(this).nestable('serialize');
    
    if (window.JSON) 
    {
      output = window.JSON.stringify(output);
      $('#choices_sorted').val(output);
    } 
    else 
    {
      alert('JSON browser support required!');      
    }    
  })
})

</script>
