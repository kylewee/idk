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

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	<h4 class="modal-title"><?php echo TEXT_SORT ?></h4>
</div>

<?php echo form_tag('menu', url_for('entities/menu')) ?>
<div class="modal-body">
  

<div class="cfg_forms_fields">
<ul id="sort_items" class="sortable">
<?php
$menu_query = db_query("select * from app_entities_menu where id='" . db_input($_GET['id']) . "'");
if($menu = db_fetch_array($menu_query))
{
	$entities_query = db_query("select * from app_entities e where e.id in (" . $menu['entities_list']. ") order by field(e.id," . $menu['entities_list'] . ")");
	while($entities = db_fetch_array($entities_query))
	{
	  echo '
	    <li id="item_' . $entities['id'] .'"><div>' . $entities['name'] . '</div></li>
	  ';
	}
}

?>
</ul>
</div>

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
          $.ajax({type: "POST",url: '<?php echo url_for("entities/menu","action=sort_items&id=" . $_GET['id'])?>',data: data});
        }
    	});
      

  });  
</script> 