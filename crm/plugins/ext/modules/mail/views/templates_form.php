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

<?php echo ajax_modal_template_header(TEXT_EXT_MAIL_TEMPLATES) ?>

<?php echo form_tag('templates_form', url_for('ext/mail/templates','action=save' . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
      
        <div class="form-group">
            <label class="col-md-3 control-label" for="accounts_id"><?php echo TEXT_EXT_MAIL_ACCOUNT ?></label>
            <div class="col-md-9">	
                <?php echo select_tag('accounts_id',mail_accounts::get_choices_by_user(),$obj['accounts_id'],array('class'=>'form-control input-xlarge required')) ?>        
            </div>			
        </div>
      
        <div class="form-group">
            <label class="col-md-3 control-label" for="subject"><?php echo TEXT_EXT_EMAIL_SUBJECT ?></label>
            <div class="col-md-9">	
                <?php echo input_tag('subject', $obj['subject'], ['class' => 'form-control required']) ?>
            </div>			
        </div>
	  
        <div class="form-group">	
            <label class="col-md-3 control-label"><?php echo TEXT_EXT_MAIL_BODY ?></label>	 	
            <div class="col-md-9">	
                <?php echo textarea_tag('body', $obj['body'], array('class' => 'editor')) ?>                
            </div>			
        </div>

                        
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

<script>
  $(function() { 
    
    $('#templates_form').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				form.submit();
			}
    });
  }); 
</script>