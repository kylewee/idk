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

<h3 class="page-title"><?php echo TEXT_HEADING_SECURITY_CONFIGURATION ?></h3>
 
<?php echo form_tag('cfg', url_for('configuration/save'),array('class'=>'form-horizontal')) ?>
<?php echo input_hidden_tag('redirect_to','configuration/attachments') ?>
<div class="form-body">
  
<h3 class="form-section">Google reCAPTCHA v2 <a href="https://www.google.com/recaptcha/about/" target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i></a></h3>
  
  <p><?php echo TEXT_RECAPTCHA_INFO ?></p>
  <p><?php echo TEXT_RECAPTCHA_HOW_ENABLE ?></p>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_STATUS ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo app_render_status_label(app_recaptcha::is_google_enabled()) ?></p>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_RECAPTCHA_SITE_KEY ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo CFG_RECAPTCHA_KEY ?></p>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_RECAPTCHA_SECRET_KEY ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo CFG_RECAPTCHA_SECRET_KEY ?></p>
    </div>			
  </div>
  
  <h3 class="form-section">Yandex SmartCaptcha <a href="https://yandex.cloud/ru/docs/smartcaptcha/quickstart" target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i></a></h3>
  
 
  <p><?php echo TEXT_YANDEX_SMARTCAPTCHA_HOW_ENABLE ?></p>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_STATUS ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo app_render_status_label(app_recaptcha::is_yandex_enabled()) ?></p>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_RECAPTCHA_SITE_KEY ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo CFG_YANDEX_SMARTCAPTCHA_KEY ?></p>
    </div>			
  </div>
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_RECAPTCHA_SECRET_KEY ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo CFG_YANDEX_SMARTCAPTCHA_SECRET_KEY ?></p>
    </div>			
  </div>

<h3 class="form-section"><?php echo TEXT_RESTRICTED_COUNTRIES ?></h3>   

	<p><?php echo TEXT_RESTRICTED_COUNTRIES_INFO ?></p>
	<p><?php echo TEXT_RESTRICTED_COUNTRIES_HOW_ENABLE ?></p>
	
	<div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_STATUS ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo app_render_status_label(app_restricted_countries::is_enabled()) ?></p>
    </div>			
  </div> 
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_ALLOWED_COUNTRIES ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo CFG_ALLOWED_COUNTRIES_LIST ?></p>
    </div>			
  </div>
  
<h3 class="form-section"><?php echo TEXT_RESTRICTED_BY_IP ?></h3>   

	<p><?php echo TEXT_RESTRICTED_BY_IP_INFO ?></p>
	<p><?php echo TEXT_RESTRICTED_BY_IP_HOW_ENABLE ?></p>
	
	<div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_STATUS ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo app_render_status_label(app_restricted_ip::is_enabled()) ?></p>
    </div>			
  </div> 
  
  <div class="form-group">
  	<label class="col-md-3 control-label" for="CFG_RESIZE_IMAGES"><?php echo TEXT_ALLOWED_IP ?></label>
    <div class="col-md-9">	
  	  <p class="form-control-static"><?php echo CFG_ALLOWED_IP_LIST ?></p>
    </div>			
  </div>    
     
</div>
</form>



