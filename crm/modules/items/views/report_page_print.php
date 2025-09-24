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
    $page = new report_page\report($report_info);
    $page->set_item($current_entity_id,$current_item_id);
    echo $page->get_html();
    
    echo ($report_info['page_orientation']=='landscape' ? '<style type="text/css" media="print"> @page { size: landscape; } </style>':'');
?>

<script>
$(function(){
    window.print()
})
</script>

