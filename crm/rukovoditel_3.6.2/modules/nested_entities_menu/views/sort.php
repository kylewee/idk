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

<?php echo form_tag('choices_form', url_for('nested_entities_menu/menu','action=sort&entities_id=' . $_GET['entities_id']),array('class'=>'form-horizontal')) ?>

<div class="modal-body">
  <div class="form-body">
 
<div class="dd" id="choices_sort">   
    <ol class="dd-list">
<?php 
$menu_query = db_query("select * from app_nested_entities_menu where entities_id='" . _get::int('entities_id') . "' order by sort_order, name");
while($v = db_fetch_array($menu_query))
{
    echo  '
        <li class="dd-item" data-id="' . $v['id'] . '">
            <div class="dd-handle" style="height: auto;">' . $v['name'] . '</div>
        </li>';
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
      group: 1,
      maxDepth: 1
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
