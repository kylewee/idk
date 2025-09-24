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
<h3 class="page-title"><?php echo TEXT_HEADING_USERS_ACCESS_GROUPS ?></h3>

<?php echo button_tag(TEXT_ADD_NEW_USER_GROUP,url_for('users_groups/form')) ?>
<?php echo ' ' . button_tag(TEXT_SORT_GROUPS,url_for('users_groups/sort')) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>    
    <th><?php echo TEXT_ID ?></th>
    <th width="100%"><?php echo TEXT_NAME ?></th>
    <th><?php echo TEXT_IS_DEFAULT ?></th>
    <th><?php echo TEXT_IS_LDAP_DEFAULT ?></th>
    <th><?php echo TEXT_SORT_ORDER ?></th>    
  </tr>
</thead>
<tbody>
<?php if(db_count('app_access_groups')==0) echo '<tr><td colspan="5">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; ?>
<?php
  $groups_query = db_fetch_all('app_access_groups','','sort_order, name');
  while($v = db_fetch_array($groups_query)):
?>
  <tr>
    <td style="white-space: nowrap;"><?php 
    echo button_icon_delete(url_for('users_groups/delete','id=' . $v['id'])) 
         . ' ' . button_icon_edit(url_for('users_groups/form','id=' . $v['id']))
         . ' ' . button_icon(TEXT_COPY, 'fa fa-files-o', url_for('users_groups/copy', 'id=' . $v['id']))
            ?></td>    
    <td><?php echo $v['id'] ?></td>
    <td><?php echo link_to($v['name'], url_for('users_groups/pivot_access_table','id=' . $v['id'])) . ' ' . tooltip_icon($v['notes']) ?></td>
    <td><?php echo render_bool_value($v['is_default']) ?></td>
    <td><?php echo render_bool_value($v['is_ldap_default']) ?></td>
    <td><?php echo $v['sort_order'] ?></td>
  </tr>
<?php endwhile?>  
</tbody>
</table>
</div>