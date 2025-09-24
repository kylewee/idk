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

<?php echo ajax_modal_template_header(TEXT_SETTINGS) ?>


<?php
    $block = export_templates_blocks::prepare_comments_block(_GET('templates_id'));
    $settings = new settings($block['settings']);
?>

<?php echo form_tag('templates_form', url_for('ext/templates_docx/blocks','templates_id=' . _GET('templates_id') . '&action=save&id=' . $block['id']),array('class'=>'form-horizontal')) ?>
<?php echo input_hidden_tag('fields_id',0) . input_hidden_tag('sort_order',0) ?>

<div class="modal-body ajax-modal-width-790">
  <div class="form-body">
    <div class="form-group">
        <label class="col-md-3 control-label" ><?php echo TEXT_INSERT ?></label>
        <div class="col-md-9">
            <?php echo '<input value="${comments}" readonly="readonly" class="form-control input-small select-all">' ?>
        </div>			
    </div>  
      
<?php
    $html = '
        <div class="form-group settings-list settings-table">
            <label class="col-md-3 control-label" for="fields_id">' . TEXT_EXT_FONT_NAME . '</label>
            <div class="col-md-4">' . input_tag('settings[font_name]',$settings->get('font_name','Times New Roman'),['class'=>'form-control input-medium required']) . tooltip_text(TEXT_EXAMPLE . ': Times New Roman, Arial') . '</div>            
          </div>

          <div class="form-group settings-list settings-table">
            <label class="col-md-3 control-label" for="fields_id">' . TEXT_EXT_FONT_SIZE . '</label>
            <div class="col-md-9">' . input_tag('settings[font_size]',$settings->get('font_size','12'),['class'=>'form-control input-small required number']) . '</div>			
          </div>
        ';
    
    echo $html;
?>
  
  </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 
