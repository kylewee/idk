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

<ul class="page-breadcrumb breadcrumb">
<?php 
	echo '
			<li>' . link_to(TEXT_EXT_MENU_IPAGES,url_for('ext/ipages/configuration')) . '<i class="fa fa-angle-right"></i></li>					
			<li>' . TEXT_ACCESS_CONFIGURATION . '</li>';
?>
</ul>
<h3 class="page-title"><?php echo TEXT_ACCESS_CONFIGURATION ?></h3>


<p><?php echo TEXT_EXT_IPAGES_ACCESS_CONFIGURATION ?></p>

<?php echo form_tag('configuration_form', url_for('ext/ipages/access_configuration','action=save'),array('class'=>'form-horizontal')) ?>
<div class="form-body">


  	<div class="form-group">
    	<label class="col-md-3 control-label" for="CFG_IPAGES_ACCESS_TO_USERS"><?php echo TEXT_USERS ?></label>
      <div class="col-md-9">	
    	  <?php echo select_tag('CFG[IPAGES_ACCESS_TO_USERS][]',[''=>TEXT_NONE]+users::get_choices(),CFG_IPAGES_ACCESS_TO_USERS,array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple')) ?>        
      </div>			
  	</div> 
  	
  	<div class="form-group">
    	<label class="col-md-3 control-label" for="CFG_IPAGES_ACCESS_TO_USERS_GROUP"><?php echo TEXT_USERS_GROUPS ?></label>
      <div class="col-md-9">	
    	  <?php echo select_tag('CFG[IPAGES_ACCESS_TO_USERS_GROUP][]',[''=>TEXT_NONE]+access_groups::get_choices(false),CFG_IPAGES_ACCESS_TO_USERS_GROUP,array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple')) ?>        
      </div>			
  	</div>  	  	  	

</div>
    

<?php echo submit_tag(TEXT_BUTTON_SAVE) ?>
</form> 
