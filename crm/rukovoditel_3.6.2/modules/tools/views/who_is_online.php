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

<h3 class="page-title"><?php echo TEXT_WHO_IS_ONLINE ?></h3>

<div class="row">
    <div class="col-md-3">
        <div class="stats-overview stat-block success">                
            <div class="details">
                <div class="title">
                    <?= TEXT_ONLINE ?> 
                </div>
                <div class="numbers">
                     <?= $count_online = who_is_online::count_online() ?>
                </div>
            </div>                
        </div>
    </div>
       
    <div class="col-md-3">
        <div class="stats-overview stat-block">                
            <div class="details">
                <div class="title">
                    <?= TEXT_OFFLINE ?> 
                </div>
                <div class="numbers">
                    <?php  
                        $count_users = db_query("select count(*) as total from app_entity_1");
                        $count = db_fetch_array($count_users);
                        echo $count['total']-$count_online;
                    ?>
                </div>
            </div>                
        </div>
    </div>
</div>    


<div class="row">
    <div class="col-md-12">
        <div id="users_listing"></div>
    </div>
</div>


<script>
    function load_items_listing(listing_container, page, search_keywords)
    {
        $('#' + listing_container).append('<div class="data_listing_processing"></div>');
        $('#' + listing_container).css("opacity", 0.5);
        
        $('#' + listing_container).load('<?php echo url_for("tools/who_is_online", 'action=listing') ?>', {page: page},
                function (response, status, xhr)
                {
                    if (status == "error")
                    {
                        $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText + '<div>' + response + '</div></div>')
                    }

                    $('#' + listing_container).css("opacity", 1);

                    appHandleUniformInListing()
                }
        );
    }


    $(function ()
    {
        load_items_listing('users_listing', 1, '');
    });


</script> 