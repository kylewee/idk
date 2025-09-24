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

<?php echo ajax_modal_template_header(TEXT_FORM_WIZARD) ?>

<?php 
$entities_id = _GET('entities_id');
$cfg = new entities_cfg($_GET['entities_id']); 

$default_selector = array('1' => TEXT_YES, '0' => TEXT_NO); 
?>

<?php echo form_tag('fields_form', url_for('entities/entities_configuration','action=save&redirect_to=entities/forms&entities_id=' . $_GET['entities_id']),array('class'=>'form-horizontal','enctype'=>'multipart/form-data')) ?>
<div class="modal-body ajax-modal-width-790">
  <div class="form-body">
    
    <p><?php echo TEXT_FORM_WIZARD_INFO ?></p> 
        
    <div class="form-group">
        <label class="col-md-4 control-label"><?php echo TEXT_IS_ACTIVE; ?></label>
        <div class="col-md-8">	
            <?php echo select_tag('cfg[is_form_wizard]', $default_selector, $cfg->get('is_form_wizard',0), array('class' => 'form-control input-small')); ?>       
        </div>			
    </div> 
    
    <div class="form-group" form_display_rules="cfg_is_form_wizard:1">
        <label class="col-md-4 control-label"><?php echo TEXT_DISPLAY_PROGRESS_BAR; ?></label>
        <div class="col-md-8">	
            <?php echo select_tag('cfg[is_form_wizard_progress_bar]', $default_selector, $cfg->get('is_form_wizard_progress_bar',0), array('class' => 'form-control input-small')); ?>       
        </div>			
    </div> 
  
    
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 