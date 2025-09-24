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

<?php 
$app_breadcrumb[] = ['title'=>$template_info['name']];
?>
<ul class="page-breadcrumb breadcrumb noprint">
    <?php echo items::render_breadcrumb($app_breadcrumb) ?>
</ul>

<h3 class="page-title"><?php echo TEXT_EXT_XML_IMPORT_PREVIEW ?></h3>

<?php 

$back_url = ($current_item_id>0 ? url_for('items/info','path=' . $app_path) : url_for('items/items','path=' . $app_path));
$import_url = url_for('items/xml_import','action=import&path=' . $app_path . '&templates_id=' . $template_info['id']);


$xml_import = new xml_import($xml_import_filename,$template_info);
$xml_import->set_preview_mode();
$xml_errors = $xml_import->has_xml_errors();

if(strlen($xml_errors))
{         
    $html = '
        <a href="' . $back_url . '" class="btn btn-default"><i class="fa fa-angle-left"></i> ' . TEXT_BUTTON_BACK. '</a><hr>
            ' . $xml_errors . '
        <hr><a href="' . $back_url . '" class="btn btn-default"><i class="fa fa-angle-left"></i> ' . TEXT_BUTTON_BACK. '</a>
        ';
    
    echo $html;
}
else
{    
    $html = form_tag('import_data',$import_url) .
    input_hidden_tag('current_time',(isset($_GET['current_time']) ? _get::int('current_time'):_post::int('current_time'))) .
    input_hidden_tag('redirect_to',($current_item_id>0 ? 'items/info':'items/items')) .
        '<a href="' . $back_url . '" class="btn btn-default btn-back"><i class="fa fa-angle-left"></i> ' . TEXT_BUTTON_BACK. '</a> 
        <button type="submit" class="btn btn-primary btn-primary-modal-action"><i class="fa fa-upload"></i> ' . TEXT_BUTTON_IMPORT . '</button>
        <div class="fa fa-spinner fa-spin primary-modal-action-loading"></div>
        <hr>
            ' . $xml_import->import_data() . '
        <hr><a href="' . $back_url . '" class="btn btn-default  btn-back"><i class="fa fa-angle-left"></i> ' . TEXT_BUTTON_BACK. '</a>
        </form>';
    
    echo $html;    
}

?>

<script>
	$(function(){
		$('#import_data').validate({
			submitHandler: function(form){
				app_prepare_modal_action_loading(form)
				$('.btn-back').hide()
				return true;
			}
		})
	})
</script>
	

