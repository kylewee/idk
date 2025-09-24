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

<h3 class="page-title"><?php echo $reports['name'] ?></h3>

<div class="row">
    <div class="col-md-12">
        <?php echo resource_timeline::render_filters_panel($reports) ?>
        <?php echo resource_timeline::render_entity_filters_panel($reports) ?>
    </div>
</div>   

<?php require(component_path('ext/resource_timeline/report'));?>

<script>
    function get_resource_timeline_height()
    {
        if($(window).height()>400)
        {
            return $(window).height()-170;
        }
        else
        {
            return $(window).height();
        }
    }
    
    $(function(){
        $(window).resize(function() {                        
            resource_timeline<?= $reports['id'] ?>.setOption('height', get_resource_timeline_height());
        });
    })
</script>
