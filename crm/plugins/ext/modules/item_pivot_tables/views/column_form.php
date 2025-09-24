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

<?php echo ajax_modal_template_header(TEXT_COLUMNS) ?>

<?php echo form_tag('reports_form', url_for('ext/item_pivot_tables/calc','action=save&reports_id=' . _get::int('reports_id') . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<?php echo input_hidden_tag('type','column') ?>
<div class="modal-body">
  <div class="form-body">
     
    
    <div class="form-group">
    	<label class="col-md-3 control-label" for="name"><?php echo TEXT_NAME ?></label>
      <div class="col-md-9">	
    	  <?php echo input_tag('name',$obj['name'],array('class'=>'form-control input-xlarge required')) ?>        
      </div>			
    </div>
    
    <div class="form-group">
    	<label class="col-md-3 control-label" for="name"><?php echo TEXT_FORMULA ?></label>
      <div class="col-md-9">	      	
    	  <?php echo textarea_tag('formula',$obj['formula'],array('class'=>'form-control required')) ?>
    	  <?php echo tooltip_text(TEXT_EXT_ITEM_PIVOT_TABLES_FORMULA_TIP) ?>        
      </div>			
    </div>
    
    <div class="form-group">
    	<label class="col-md-3 control-label" for="name"><?php echo TEXT_SORT_ORDER ?></label>
      <div class="col-md-9">	
    	  <?php echo input_tag('sort_order',$obj['sort_order'],array('class'=>'form-control input-small')) ?>        
      </div>			
    </div>
         
  </div>  
</div>
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() { 
    $('#reports_form').validate();    
  });
</script>  

 