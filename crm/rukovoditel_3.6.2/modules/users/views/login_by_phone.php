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

<h3 class="form-title"><?php echo TEXT_LOGIN_BY_PHONE_NUMBER ?></h3>

<p><?php echo TEXT_ENTER_YOUR_PHONE_NUMBER ?></p>

<?php echo form_tag('login_form', url_for('users/login_by_phone','action=login'),array('class'=>'login-form')) ?>

<div class="form-group">	
	<div class="input-icon">
		<i class="fa fa-phone"></i>
		<input class="form-control placeholder-no-fix required" type="text" autocomplete="off" name="phone" id="phone" />
	</div>
</div>

<?php 
	$cfg =  new fields_types_cfg($app_fields_cache[1][CFG_2STEP_VERIFICATION_USER_PHONE]['configuration']);
	
	if(strlen($cfg->get('mask'))>0)
	{
		echo '
        <script>
          jQuery(function($){
             $("#phone").mask("' . $cfg->get('mask') . '");
          });
        </script>
      ';
	}
?>

<?php if(app_recaptcha::is_enabled()): ?>
<div class="form-group">
	<?php echo app_recaptcha::render() ?>	
</div>
<?php endif ?>

<div class="form-actions">
	
	<button type="button" id="back-btn" class="btn btn-default" onClick="location.href='<?php echo url_for('users/login')?>'"><i class="fa fa-arrow-circle-left"></i> <?php echo TEXT_BUTTON_BACK ?></button>
	
	<button type="submit" class="btn btn-info pull-right"><?php echo TEXT_BUTTON_LOGIN ?></button>
</div>

</form>

<script>
  $(function() { 
    $('#login_form').validate();                                                                            
  });    
</script> 