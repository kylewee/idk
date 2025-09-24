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

<?php echo form_tag('choices_form', url_for('entities/user_roles','action=sort&entities_id=' . $_GET['entities_id'] . '&fields_id=' . $_GET['fields_id']),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
 
<div class="dd" id="choices_sort">      
<?php 
$count_query = db_query("select count(*) as total from app_user_roles where fields_id = '" . db_input(_get::int('fields_id')) . "' order by sort_order, name");
$count = db_fetch_array($count_query);

if($count['total']>0)
{
	$html = '<ol class="dd-list">';

	$choices_query = db_query("select * from app_user_roles where fields_id = '" . db_input(_get::int('fields_id')). "' order by sort_order, name");

	while($v = db_fetch_array($choices_query))
	{
		$html .= '<li class="dd-item" data-id="' . $v['id'] . '"><div class="dd-handle">' . $v['name'] . '</div></li>';
	}

	$html .= '</ol>';
	
	echo $html;
}
?>
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