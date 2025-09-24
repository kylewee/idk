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

<div class="forget-password guest-login">
    <?php        
        echo '<a href="' . url_for('users/guest_login') . '" class="btn btn-social btn-guest-login"><span><i class="fa fa-user" aria-hidden="true"></i></span> ' . (strlen(CFG_GUEST_LOGIN_BUTTON_TITLE) ? CFG_GUEST_LOGIN_BUTTON_TITLE : TEXT_LOGIN_AS_GUEST) . '</a>';  
    ?>    
</div>