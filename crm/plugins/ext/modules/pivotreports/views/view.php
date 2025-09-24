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

$check = db_count('app_ext_pivotreports_fields',$pivotreports['id'],'pivotreports_id');

if($check==0)
{
	
	echo '
			<h3 class="page-title">' . $pivotreports['name'] . '</h3>
			<div class="alert alert-warning">' . TEXT_EXT_PIVOTREPORTS_FIELDS_ERROR . '</div>
	';	
}
else 
{	
	//allow edit
	$pivotreports = pivotreports::apply_allow_edit($pivotreports);
	
	$print_btn = '';
	
	if($app_user['group_id']==0 or $pivotreports['allow_edit']==1)
	{
		$print_btn .= '&nbsp;
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-default ' . ($pivotreports['view_mode']==0 ? 'active':'') . '">
					<input name="pivot_view_mode" type="radio" value="0" class="toggle pivot_view_mode">' . TEXT_BUTTON_EDIT. '</label>
					<label class="btn btn-default ' . ($pivotreports['view_mode']==1 ? 'active':'') . '">
					<input name="pivot_view_mode" type="radio" value="1" class="toggle pivot_view_mode">' . TEXT_VIEW . '</label>				
				</div>&nbsp;';
	}
				
	$print_btn .= '<button type="button" class="btn btn-default btn-template-print"><i class="fa fa-print" aria-hidden="true"></i></button>';
	
	echo '<h3 class="page-title noprint">' . $pivotreports['name'] . ' ' . $print_btn . '</h3>';
	
	//print_rr($pivotreports);
	
	$filters_preivew = new filters_preivew($fiters_reports_id);
	$filters_preivew->redirect_to = 'pivotreports' . $_GET['id'];
	$filters_preivew->has_listing_configuration = false;
		
	echo $filters_preivew->render();
	
	if(($app_user['group_id']==0 or $pivotreports['allow_edit']==1) and $pivotreports['view_mode']==0)
	{
		require(component_path('ext/pivotreports/pivotuitable'));
	}
	else
	{
		require(component_path('ext/pivotreports/pivottable'));
	}
}

?>


<?php echo form_tag('pivot-export-form', url_for('ext/pivotreports/view','id=' . $pivotreports['id'] . '&action=print')) ?>
	<?php echo textarea_tag('pivot_export_content','',array('style'=>'display:none')) ?>
</form>  

<script>  
  $('.btn-template-print').click(function(){	
    /*	    
  	$('#pivot_export_content').val('<table class="pvtTable">'+$('.pvtTable').html()+'</table>');
    $('#pivot-export-form').attr('target','_new')
    $('#pivot-export-form').submit();
    */
    $('.form-control').addClass('noprint')
  	window.print();
  })
  
  $('.pivot_view_mode').change(function(){
		location.href="<?php echo url_for('ext/pivotreports/view','id=' . $pivotreports['id'] . '&action=set_view_mode') ?>&view_mode="+$(this).val();
  })
</script>
