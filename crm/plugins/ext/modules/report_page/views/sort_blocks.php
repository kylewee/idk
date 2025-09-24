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

<?php echo ajax_modal_template_header(TEXT_SORT_ORDER) ?>

<?php 

echo form_tag('templates_filter',url_for('ext/report_page/' . $app_redirect_to,'block_id=' . _GET('block_id'))) ?>

<div class="modal-body">

<ul id="blocks" class="sortable">
<?php
$parent_id = $_GET['block_id']??0;
$block_type = $_GET['block_type']??'';
$blocks_query = db_query("select b.*,f.type as field_type, f.name as field_name, f.entities_id as field_entity_id from app_ext_report_page_blocks b left join app_fields f on b.field_id=f.id  where block_type='" . db_input($block_type) . "' and b.parent_id = " . db_input($parent_id) . " order by b.sort_order, b.id");
while($blocks = db_fetch_array($blocks_query))
{
    $settings = new settings($blocks['settings']);
    echo '<li id="blocks_' . $blocks['id'] . '">' . (strlen($settings->get('heading')) ? $settings->get('heading') : $blocks['name']) . ' (#' . $blocks['id'] . ')</li>';
}
?>
</ul>
</div>

<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
  $(function() {         
    	$( "ul.sortable" ).sortable({
    		connectWith: "ul",
    		update: function(event,ui){  
          data = '';  
          $( "ul.sortable" ).each(function() {data = data +'&'+$(this).attr('id')+'='+$(this).sortable("toArray") });                            
          data = data.slice(1)                      
          $.ajax({type: "POST",url: '<?php echo url_for("ext/report_page/reports","action=sort_blocks")?>',data: data});
        }
    	});      
  });  
</script>