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

switch($app_module_action)
{
    case 'listing':
        $html = '
            <div class="table-scrollable">
                    <table class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                            <th>' . TEXT_STATUS . '</th>
                            <th>' . TEXT_FIELDTYPE_USER_ACCESSGROUP_TITLE . '</th>
                            <th width="100%">' . TEXT_NAME . '</th>		                                                       
                            <th>' . TEXT_EMAIL . '</th>
                      </tr>
                    </thead>
                    <tbody>
		';

        $where_sql = '';

        $order_by_sql = " order by" . (CFG_APP_DISPLAY_USER_NAME_ORDER == 'firstname_lastname' ? ' e.field_7, e.field_8' : ' e.field_8, e.field_7');

        $listing_sql = "select e.* from app_entity_1 e, app_who_is_online o  where e.id=o.users_id and o.date_updated>=" . (time()-(CFG_WHO_IS_ONLINE_INTERVAL*60) . $order_by_sql);
        $listing_split = new split_page($listing_sql, 'users_listing', '', CFG_APP_ROWS_PER_PAGE);
        $items_query = db_query($listing_split->sql_query);
        while($item = db_fetch_array($items_query))
        {
            $user = $app_users_cache[$item['id']]??[];
            
            $html .= '
                <tr>
                    <td><span class="label label-success">' . TEXT_ONLINE . '</span></td>			
                    <td>' . $user['group_name'] .  '</td>                    
                    <td>' . link_to($user['name'],url_for('items/info','path=1-' . $item['id'])) .  '</td>    
                    <td>' . $user['email'] .  '</td>
                </tr>
		';
        }

        if($listing_split->number_of_rows == 0)
        {
            $html .= '
                <tr>
                  <td colspan="6">' . TEXT_NO_RECORDS_FOUND . '</td>
                </tr>
            ';
        }

        $html .= '
		  </tbody>
		</table>
		</div>
		';

        //add pager
        $html .= '
		  <table width="100%">
		    <tr>
		      <td>' . $listing_split->display_count() . '</td>
		      <td align="right">' . $listing_split->display_links() . '</td>
		    </tr>
		  </table>
		';

        echo $html;

        exit();

        break;
}