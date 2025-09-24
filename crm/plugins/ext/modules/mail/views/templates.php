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

<ul class="page-breadcrumb breadcrumb">
    <?php
    $folders = mail_accounts::get_folders_choices();

    echo '
			<li>' . link_to($folders[$app_mail_filters['folder']], url_for('ext/mail/accounts')) . '<i class="fa fa-angle-right"></i></li>
			<li>' . TEXT_EXT_MAIL_TEMPLATES . '</li>';
    ?>
</ul>

<p><?php echo TEXT_EXT_MAIL_TEMPLATES_INFO ?></p>

<?php echo button_tag(TEXT_BUTTON_ADD, url_for('ext/mail/templates_form'), true) ?>

<div class="table-scrollable">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th style="width: 90px;"><?php echo TEXT_ACTION ?></th>
                <th><?= TEXT_EXT_MAIL_ACCOUNT ?></th>
                <th><?php echo TEXT_EXT_EMAIL_SUBJECT ?></th>
                <th><?php echo TEXT_EXT_MAIL_BODY ?></th>           
            </tr>
        </thead>
        <tbody>
            <?php
            $templates_query = db_query("select mf.*, ma.name as account_name from app_ext_mail_templates mf left join app_ext_mail_accounts ma on mf.accounts_id=ma.id where  mf.accounts_id in (select accounts_id from app_ext_mail_accounts_users where users_id='" . $app_user['id'] . "') order by mf.id");

            if(db_num_rows($templates_query) == 0)
                echo '<tr><td colspan="7">' . TEXT_NO_RECORDS_FOUND . '</td></tr>';

            while($templates = db_fetch_array($templates_query)):                               
                ?>
                <tr>
                    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/mail/templates_delete', 'id=' . $templates['id'])) . ' ' . button_icon_edit(url_for('ext/mail/templates_form', 'id=' . $templates['id'])) ?></td>  
                    <td><?php echo $templates['account_name'] ?></td>
                    <td class="white-space-nomral" style="min-width: 250px;"><?php echo $templates['subject'] ?></td>      
                    <td class="white-space-nomral"><?php echo truncated_text_block($templates['body']) ?></td>      
                </tr>  
            <?php endwhile ?>
        </tbody>
    </table>
</div>

<script>
    appHandleTruncatedText()
</script>