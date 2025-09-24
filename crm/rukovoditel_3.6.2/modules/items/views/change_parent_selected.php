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

<?php echo ajax_modal_template_header(TEXT_CHANGE_PARENT_ITEM) ?>

<?php
if(!isset($app_selected_items[$_GET['reports_id']])) $app_selected_items[$_GET['reports_id']] = array();

if(count($app_selected_items[$_GET['reports_id']])==0)
{
  echo '
    <div class="modal-body">    
      <div>' . TEXT_PLEASE_SELECT_ITEMS . '</div>
    </div>    
  ' . ajax_modal_template_footer('hide-save-button');
}
else
{
?>

<?php echo form_tag('modal_form',url_for('items/change_parent_selected','path=' . $app_path . '&action=change_parent')) . input_hidden_tag('reports_id',_GET('reports_id')) ?>
<div class="modal-body ajax-modal-width-790"> 
        
    <div class="dd" id="choices_sort">  
        <?php  echo select_entities_tag('parent_id',[],'',['entities_id'=>$current_entity_id,'is_tree_view'=>true,'parent_item_id'=>$parent_entity_item_id])  ?>
    </div>
</div>

<?php 
    $count_selected_text = sprintf(TEXT_SELECTED_RECORDS,count($app_selected_items[$_GET['reports_id']]));
    echo ajax_modal_template_footer('','',$count_selected_text); 
?>
</form>

<script>
    $(function(){
        $('#modal_form').validate({igonre:''})
    })
</script>
 
<?php
}