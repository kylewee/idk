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

    $img = '<img style="width: 100%; max-width: 250px;" src="images/rukovoditel_box' . (APP_LANGUAGE_SHORT_CODE == 'ru' ? '.ru' : '') . '.png">';
?>
<h3 class="page-title"><?php echo TEXT_ABOUT_APP ?></h3>

<div class="row">    
    <div class="col-md-12">
        <div class="col-md-3"><center><?= $img ?></center></div>
        <div class="col-md-9">
            <?= TEXT_ABOUT_APP_DETAILS . '<hr>' ?>
            <?= TEXT_CURRENT_APP_VERSION . ': <b>' . PROJECT_VERSION . (strlen(PROJECT_VERSION_DEV) ? ' (' . PROJECT_VERSION_DEV . ')':'') . '</b><br>' . TEXT_UPDATE_INSTRUCTION ?>
            
        </div>
    </div>
</div>
