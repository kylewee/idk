<?php
/*
 *  Этот файл является частью программы "CRM Руководитель" - конструктор CRM систем для бизнеса
 *  https://www.rukovoditel.net.ru/
 *  
 *  CRM Руководитель - это свободное программное обеспечение, 
 *  распространяемое на условиях GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 *  
 *  Автор и правообладатель программы: Харчишина Ольга Александровна (RU), Харчишин Сергей Васильевич (RU).
 *  Государственная регистрация программы для ЭВМ: 2023664624
 *  https://fips.ru/EGD/3b18c104-1db7-4f2d-83fb-2d38e1474ca3
 */
?>

<div class="table-scrollable">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th><?php echo TEXT_ACTION ?></th>    
                <th><?php echo TEXT_ID ?></th>
                <th><?php echo TEXT_REPORT_ENTITY ?></th>        
                <th width="100%"><?php echo TEXT_NAME ?></th>
                <th><?php echo TEXT_EXT_PROCESS_BUTTON_TITLE ?></th>                    
                <th><?php echo TEXT_COMMENT ?></th>    
                <th><?php echo TEXT_IS_ACTIVE ?></th>
                <th><?php echo TEXT_SORT_ORDER ?></th>            
            </tr>
        </thead>
        <tbody>
            <?php if(db_count('app_ext_processes') == 0) echo '<tr><td colspan="8">' . TEXT_NO_RECORDS_FOUND . '</td></tr>'; ?>
            <?php
            $where_sql = '';

            if($processes_filter > 0)
            {
                $where_sql .= " and p.entities_id='" . db_input($processes_filter) . "'";
            }
            
            if(strlen($processes_search_filter))
            {
                $where_sql .= " and (p.name like '%" . db_input($processes_search_filter) . "%' or p.button_title like '%" . db_input($processes_search_filter) . "%')";
            }
            
            $listing_sql = "select p.*, e.name as entities_name from app_ext_processes p, app_entities e where e.id=p.entities_id {$where_sql} order by  e.name, p.sort_order, p.name";
            $listing_split = new split_page($listing_sql,'process_buttons_listing','',CFG_APP_ROWS_PER_PAGE);
            $processes_query = db_query($listing_split->sql_query);
            while($v = db_fetch_array($processes_query)):
                ?>
                <tr>
                    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/processes/delete', 'id=' . $v['id'])) . ' ' . button_icon_edit(url_for('ext/processes/form', 'id=' . $v['id'])) . ' ' . button_icon(TEXT_COPY, 'fa fa-files-o', url_for('ext/processes/copy', 'id=' . $v['id'])) . ' ' . button_icon(TEXT_BUTTON_CONFIGURE_FILTERS, 'fa fa-cogs', url_for('default_filters/filters', 'reports_id=' . default_filters::get_reports_id($v['entities_id'], 'process' . $v['id']) . '&redirect_to=process' . $v['id']), false); ?></td>        
                    <td><?php echo $v['id'] ?></td>
                    <td><?php echo $v['entities_name'] ?></td>    
                    <td><?php
            echo link_to($v['name'], url_for('ext/processes/actions', 'process_id=' . $v['id'])) . ' ' . tooltip_icon($v['notes']);

            $count_query = db_query("select count(*) as total from app_ext_processes_actions pa where pa.process_id='" . $v['id'] . "'");
            $count = db_fetch_array($count_query);

            $html = TEXT_EXT_COUNT_ACTIONS . ': ' . $count['total'] . '&nbsp;|&nbsp;' . link_to(TEXT_FILTERS, url_for('default_filters/filters', 'reports_id=' . default_filters::get_reports_id($v['entities_id'], 'process' . $v['id']) . '&redirect_to=process' . $v['id'])) . ': ' . reports::count_filters_by_reports_type($v['entities_id'], 'process' . $v['id']);

            if(process_form::has_editable_fields($v['id']))
            {
                $html .= '&nbsp;|&nbsp;<a href="' . url_for('ext/processes/process_form', 'process_id=' . $v['id']) . '">' . TEXT_NAV_FORM_CONFIG . '</a>';
            }

            echo tooltip_text($html);
                ?></td>
                    <td><?php echo $v['button_title'] ?></td>      	
                    <td><?php echo render_bool_value($v['allow_comments']) ?></td>  	
                    <td><?php echo render_bool_value($v['is_active']) ?></td>
                    <td><?php echo $v['sort_order'] ?></td>

                </tr>
<?php endwhile ?>  
        </tbody>
    </table>
</div>

<table width="100%">
    <tr>
        <td><?= $listing_split->display_count() ?></td>
        <td align="right"><?= $listing_split->display_links() ?></td>
    </tr>
</table>


