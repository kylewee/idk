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


class mail_templates
{
    static function render_dropdown_helper()
    {
        global $app_user;
        
        $templates_query = db_query("select mf.*, ma.name as account_name from app_ext_mail_templates mf left join app_ext_mail_accounts ma on mf.accounts_id=ma.id where  mf.accounts_id in (select accounts_id from app_ext_mail_accounts_users where users_id='" . $app_user['id'] . "') order by mf.id");
        if(db_num_rows($templates_query)==0) return '';
        
        $html = '
            
            <div class="dropdown">
                      <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        ' .TEXT_EXT_TEMPLATES . '
                        <span class="caret"></span>
                      </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1" style="max-height: 250px; overflow-y: auto">
            ';
        
        while($templates = db_fetch_array($templates_query))
        {
            $html .= '
                <li>
                    <a href="#" class="mail-template-button" data-id="' . $templates['id'] . '">' . $templates['subject']. '</a>
                </li>    
                ';
        }
        
        $html .= '</ul></div>';
        
        return $html;
    }
}
