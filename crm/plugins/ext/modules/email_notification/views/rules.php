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

<?php require(component_path('entities/navigation')) ?>

<h3 class="page-title"><?php echo TEXT_EXT_EMAIL_NTOFICATION ?></h3>

<p><?php echo TEXT_EXT_EMAIL_NTOFICATION_INFO ?></p>
<p><?= TEXT_NOTIFICATIONS_SCHEDULE_TIP ?> <br><code><?= DIR_FS_CATALOG . 'cron/email_notification.php' ?></code></p>

<?php echo button_tag(TEXT_BUTTON_CREATE, url_for('ext/email_notification/form', 'entities_id=' . _get::int('entities_id')), true) ?>

<div class="table-scrollable">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th><?php echo TEXT_ACTION ?></th>
                <th><?php echo TEXT_ID ?></th>
                <th><?php echo TEXT_REPORT_ENTITY ?></th>                
                <th><?php echo TEXT_EXT_RULE ?></th>                        
                <th width="100%"><?php echo TEXT_EMAIL_SUBJECT ?></th>
                <th><?php echo TEXT_DAY ?></th>
                <th><?php echo TEXT_TIME ?></th>
                <th><?php echo TEXT_IS_ACTIVE ?></th>                
            </tr>
        </thead>
        <tbody>
            <?php
            $fields_cahce = fields::get_name_cache();

            $rules_query = db_query("select r.*, e.name as entity_name from app_ext_email_notification_rules r, app_entities e where e.id=r.entities_id and e.id='" . _get::int('entities_id') . "' order by r.id");

            if(db_num_rows($rules_query) == 0)
            {
                echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND . '</td></tr>';
            }

            while($rules = db_fetch_array($rules_query)):
                ?>
                <tr>
                    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/email_notification/delete', 'id=' . $rules['id'] . '&entities_id=' . _get::int('entities_id'))) . ' ' . button_icon_edit(url_for('ext/email_notification/form', 'id=' . $rules['id'] . '&entities_id=' . _get::int('entities_id'))) ?></td>
                    <td><?php echo $rules['id'] ?></td>
                    <td><?php echo $rules['entity_name'] ?></td>                    
                    <td><?php echo email_notification_rules::get_action_type_name($rules['action_type']) . ' ' . tooltip_icon($rules['notes'])  ?>:<br>  
                    <?php
            $html = [];
            if(strlen($rules['send_to_users']))
            {
                foreach(explode(',', $rules['send_to_users']) as $v)
                {
                    if(strlen($name = users::get_name_by_id($v)))
                        $html[] = $name;
                }
            }
            elseif(strlen($rules['send_to_user_group']))
            {                
                foreach(explode(',',$rules['send_to_user_group']) as $v)
                {
                    $html[] = access_groups::get_name_by_id($v);
                }
            }
            elseif(strlen($rules['send_to_email']))
            {
                $html[] = nl2br($rules['send_to_email']);
            }
            

            if(count($html))
            {
                echo implode('<br>', $html);
            }
                ?></td>    
                    <td>
                        <?php 
                            $reports_id = default_filters::get_reports_id($rules['entities_id'], 'email_notification' . $rules['id']);
                            $count_filters = reports::count_filters_by_reports_id($reports_id);
                            
                            $html = $rules['subject'];
                            $html .= '<br><span class="' . (!$count_filters ? 'label label-warning':''). '">' . link_to(TEXT_FILTERS . ' (' . $count_filters . ')', url_for('default_filters/filters','reports_id=' . $reports_id . '&redirect_to=email_notification' . $rules['id'])) . '</span>';
                            $html .= ' | ' . link_to_modalbox(TEXT_SORT_ORDER, url_for('reports/sorting','reports_id=' . $reports_id . '&redirect_to=email_notification' . $rules['id']));
                            $html .= ' | ' . link_to_modalbox(TEXT_BUTTON_SEND_TEST_EMAIL, url_for('ext/email_notification/send_test','id=' . $rules['id'] . '&entities_id=' . $rules['entities_id']));
                            
                            echo $html;
                        ?>
                    </td>
                    <td><?= (strlen($rules['notification_days']) ? implode('<br>', array_map(function($v){ return app_get_days_choices()[$v]; },explode(',',$rules['notification_days']))) : TEXT_EVERY_DAY)  ?></td>
                    <td><?= implode('<br>', array_map(function($v){ return $v .':00'; },explode(',',$rules['notification_time']))) ?></td>
                    <td><?php echo render_bool_value($rules['is_active'], true) ?></td>
                </tr>  
<?php endwhile ?>
        </tbody>
    </table>
</div>