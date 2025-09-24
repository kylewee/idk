
<?php require(component_path('entities/navigation')) ?>

<h3 class="page-title"><?php echo  TEXT_COMPOSITE_UNIQUE_FIELDS ?></h3>

<p><?php echo TEXT_COMPOSITE_UNIQUE_FIELDS_INFO ?></p>

<?php echo button_tag(TEXT_BUTTON_ADD_NEW_RULE,url_for('composite_unique_fields/rules_form','entities_id=' . $_GET['entities_id'] . '&parent_id=0'),true) . ' ' ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    
    <th><?php echo TEXT_ACTION?></th>
    <th>#</th>    
    <th><?php echo TEXT_IS_ACTIVE ?></th>
    <th><?php echo TEXT_FIELD . ' 1' ?></th>
    <th><?php echo TEXT_FIELD . ' 2' ?></th>    
    <th width="100%"><?php echo TEXT_MESSAGE_TEXT ?></th>               
  </tr>
</thead>
<tbody>
<?php


$row_query = db_query("select * from app_composite_unique_fields where entities_id=" . _GET('entities_id'));
        
if(!db_num_rows($row_query)) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($v = db_fetch_array($row_query))
{
    
    $f1 = db_find('app_fields', $v['field_1']);
    $f2 = db_find('app_fields', $v['field_2']);
?>
<tr>  
  <td style="white-space: nowrap;">
      <?php echo button_icon_delete(url_for('composite_unique_fields/rules_delete','id=' . $v['id'] . '&entities_id=' . $_GET['entities_id'])) . ' ' . 
          button_icon_edit(url_for('composite_unique_fields/rules_form','id=' . $v['id']. '&entities_id=' . $_GET['entities_id']))  ?></td>
  <td><?php echo $v['id'] ?></td>
  <td><?php echo render_bool_value($v['is_active']) ?></td>
  <td><?= fields_types::get_option($f1['type'],'name',$f1['name']) ?></td>  
  <td><?= fields_types::get_option($f2['type'],'name',$f2['name']) ?></td>  
  <td class="white-space-normal"><?= $v['message'] ?></td>             
</tr>  
<?php } ?>
</tbody>
</table>
</div>
