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

<h3 class="page-title"><?php echo  TEXT_EXT_AVAILABLE_RSS_FEEDS ?></h3>

<p><?php echo TEXT_EXT_AVAILABLE_RSS_FEEDS_INFO ?></p>

<div class="table-scrollable">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>  
                <th><?php echo TEXT_NAME ?></th>        
                <th width="100%"><?php echo TEXT_URL ?></th>                    
            </tr>
        </thead>
        <tbody>
            <?php
            $feeds_query = db_query("select id,name,rss_id from app_ext_rss_feeds where find_in_set(" . $app_user['group_id'] . ",users_groups) or find_in_set(" . $app_user['id'] . ",assigned_to) order by type, sort_order,name");

            if(db_num_rows($feeds_query) == 0)
                echo '<tr><td colspan="7">' . TEXT_NO_RECORDS_FOUND . '</td></tr>';

            while($feeds = db_fetch_array($feeds_query))
            {
                $url = url_for('feeders/rss','client=' . $app_user['client_id'] . '&rss=' . $feeds['rss_id']);
                ?>
                <tr>                    
                    <td><?php echo $feeds['name'] ?></td>  
                    <td><?php echo input_tag('url[]',$url,['class'=>'form-control select-all','readonly'=>'readonly']) ?></td>
                </tr>  
            <?php } ?>
        </tbody>
    </table>
</div>