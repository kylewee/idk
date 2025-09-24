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

<?php $account = db_find('app_ext_mail_accounts',$_GET['id']); ?>

<?php echo ajax_modal_template_header($account['name']) ?>

<form class="form-horizontal">
<div class="modal-body">
  <div class="form-body">
      
    <div class="form-group">
    	<label class="col-md-4 control-label" for="name"><?php echo TEXT_EMAIL_USE_SMTP ?></label>
      <div class="col-md-8">	
      	<p class="form-control-static"><?php echo render_bool_value($account['use_smtp']) ?></p>    	         
      </div>			
    </div>
                
    <div class="form-group">
    	<label class="col-md-4 control-label" for="name"><?php echo TEXT_EMAIL_SMTP_SERVER ?></label>
      <div class="col-md-8">	
      	<p class="form-control-static"><?php echo $account['smtp_server'] ?></p>    	         
      </div>			
    </div>
    
    <div class="form-group">
    	<label class="col-md-4 control-label" for="name"><?php echo TEXT_EMAIL_SMTP_PORT ?></label>
      <div class="col-md-8">	
      	<p class="form-control-static"><?php echo $account['smtp_port'] ?></p>    	         
      </div>			
    </div>
    
    <div class="form-group">
    	<label class="col-md-4 control-label" for="name"><?php echo TEXT_EMAIL_SMTP_ENCRYPTION ?></label>
      <div class="col-md-8">
      	<p class="form-control-static"><?php echo $account['smtp_encryption'] ?></p>	    	         
      </div>			
    </div>
    
    <div class="form-group">
    	<label class="col-md-4 control-label" for="name"><?php echo TEXT_EMAIL_SMTP_LOGIN ?></label>
      <div class="col-md-8">
      	<p class="form-control-static"><?php echo $account['smtp_login'] ?></p>	    	         
      </div>			
    </div>
    
    <div class="form-group">
    	<label class="col-md-4 control-label" for="name"><?php echo TEXT_STATUS ?></label>
      <div class="col-md-8">	
    	  <?php
    	  
    	  $options = [
    	  		'from' => (strlen($account['email']) ? $account['email'] : $account['login']),
    	  		'from_name' => $account['name'],
    	  		'to' => $app_user['email'],
    	  		'to_name' => $app_user['name'],
    	  		'subject' => TEXT_TEST_EMAIL_SUBJECT . ' | ' . $account['name'],
    	  		'body' => TEXT_TEST_EMAIL_SUBJECT,
    	  ];
    	      	      	 
    	  $result = mail_accounts::send_mail($account, $options);
    	  
    	  if($result['status']=='error')
    	  {
    	  	echo '<div class="alert alert-danger">' . $result['text'] . '</div>';
    	  }
    	  else
    	  {
    	  	echo '<div class="alert alert-success">' . TEXT_OK. '</div>';
    	  }
    	  ?>        
      </div>			
    </div>
    
        
   </div>
</div> 
</form>

<?php echo ajax_modal_template_footer('hide-save-button') ?>