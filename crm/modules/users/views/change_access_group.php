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

<?php echo ajax_modal_template_header(TEXT_LOGIN_AS_USER) ?>

<div class="modal-body">    
<?php
    foreach(explode(',',$app_user['multiple_access_groups']) as $group_id)
    {
        if($group_id!=$app_user['group_id'])
        {
            echo '<a href="' . url_for('users/change_access_group', 'action=change&id=' . $group_id) . '" class="btn btn-primary btn-block">' . access_groups::get_name_by_id($group_id) . '</a>';
        }
    }
?>

</div>

<?php echo ajax_modal_template_footer('hide-save-button') ?>