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

<?php if(in_array($app_module_path,['ext/pivotreports/view','dashboard/dashboard','dashboard/reports','dashboard/reports_groups'])): ?>
<link rel="stylesheet" type="text/css" href="js/pivottable-master/dist/pivot.css">
<link rel="stylesheet" type="text/css" href="js/pivottable-master/dist/c3.min.css">
<?php endif ?>


<?php if(in_array($app_module_path,['report_page/view','ext/graphicreport/view','ext/funnelchart/view','dashboard/dashboard','dashboard/reports','dashboard/reports_groups','ext/pivot_tables/view']) ): ?>
<link rel="stylesheet" type="text/css" href="js/highcharts/11.0.0/highcharts.css">
<?php endif ?>

<?php if($app_module=='timeline_reports' and $app_action=='view'): ?>
<link rel="stylesheet" type="text/css" href="js/timeline-2.9.1/timeline.css">
<?php endif ?>

<link rel="stylesheet" type="text/css" href="js/app-chat/app-chat.css?v=2">

<link rel="stylesheet" type="text/css" href="js/app-mail/app-mail.css?v=1">

<!-- pivot table -->
<link href="js/webdatarocks/1.3.3/webdatarocks.min.css" rel="stylesheet" />