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
<h3 class="page-title"><?php echo TEXT_BUTTON_SEND_TEST_EMAIL ?></h3>

<p><?php echo sprintf(TEXT_SEND_TEST_EMAIL_INFO,TEXT_TEST_EMAIL_SUBJECT) ?></p>

<?php echo form_tag('cfg', url_for('configuration/emails_send_test','action=send'), array('class' => 'form-horizontal')) ?>

<div class="form-body">        
    <div class="form-group">
        <label class="col-md-2 control-label" ><?php echo TEXT_EMAIL ?></label>
        <div class="col-md-10">	
            <?php echo input_tag('send_to', $_GET['send_to']??$app_user['email'], array('class' => 'form-control input-xlarge required email')); ?> 
        </div>			
    </div>
        
    <?php echo submit_tag(TEXT_BUTTON_SEND) ?>
    
</div>
</form>

<script>
$('#cfg').validate()
</script>
