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

<?php echo ajax_modal_template_header(TEXT_CONFIGURE_THEME) ?>

<?php echo form_tag('dashboard', url_for('dashboard/'),['class'=>'form-horizontal']) ?>
<div class="modal-body">
			
		<div class="form-group">
			<label class="col-md-4 "><?php echo TEXT_SIDEBAR ?></label>	
      		<div class="col-md-8">
      			<?php echo  select_tag('sidebar-option',array('default'=>TEXT_DEFAULT,'fixed'=>TEXT_SIDEBAR_FIXED),($app_users_cfg->get('sidebar-option')=='page-sidebar-fixed' ? 'fixed':'default'),array('class'=>'sidebar-option form-control input-medium','onChange'=>"set_user_cfg('sidebar-option',this.value)")) ?>
      		</div>	
		</div>
      
		<div class="form-group">
			<label class="col-md-4 "><?php echo TEXT_SIDEBAR_POSITION ?></label>	
      		<div class="col-md-8">
      			<?php echo select_tag('sidebar-pos-option',array('left'=>TEXT_SIDEBAR_POS_LEFT,'right'=>TEXT_SIDEBAR_POS_RIGHT),($app_users_cfg->get('sidebar-pos-option')=='page-sidebar-reversed' ? 'right':'left'),array('class'=>'sidebar-pos-option form-control input-medium','onChange'=>"set_user_cfg('sidebar-pos-option',this.value)")) ?>
      		</div>			
		</div>
			
		<div class="form-group">
			<label class="col-md-4 "><?php echo TEXT_SCALE ?></label>	
      		<div class="col-md-8">	
      			<?php echo select_tag('page-scale-option',array('default'=>TEXT_DEFAULT,'reduced'=>TEXT_SCALE_REDUCED),($app_users_cfg->get('page-scale-option')=='page-scale-reduced' ? 'reduced':'default'),array('class'=>'scale-option  form-control input-medium','onChange'=>"set_user_cfg('page-scale-option',this.value)")) ?>
      		</div>	
		</div>

</div> 

<?php echo ajax_modal_template_footer() ?>

</form>

