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

class mail_related
{

    public $has_access, $entities_id, $position, $accounts_entities;

    function __construct($entities_id, $position)
    {
        global $app_user;

        $this->has_access = false;
        $this->entities_id = $entities_id;
        $this->position = $position;

        $accounts_entities_query = db_query("select ae.* from app_ext_mail_accounts_entities ae where ae.entities_id='" . $this->entities_id . "' and ae.related_emails_position='" . $position . "' and ae.accounts_id in (select au.accounts_id from app_ext_mail_accounts_users au where au.users_id='" . $app_user['id'] . "')");
        if($accounts_entities = db_fetch_array($accounts_entities_query))
        {
            $this->has_access = true;

            $this->accounts_entities = $accounts_entities;
        }
    }

    function render_list($items_id, $item_info=[])
    {
        global $app_path;
        
        if(!$this->has_access)
            return false;

        $related_mails_query = db_query("select mg.id, mg.subject_cropped, (select count(*) from app_ext_mail m3 where m3.groups_id=mg.id) as count_mails, (select count(*) from app_ext_mail m2 where m2.groups_id=mg.id and m2.is_new=1) as is_new,(select date_added from app_ext_mail m1 where m1.groups_id=mg.id order by date_added desc limit 1) as date_added from app_ext_mail_to_items m2i left join app_ext_mail_groups mg on mg.id=m2i.mail_groups_id where m2i.entities_id='" . $this->entities_id . "' and m2i.items_id='" . $items_id . "'");
        $count_mails = db_num_rows($related_mails_query);
                
        $is_collapsed = $count_mails>0 ? false:true;
        
        $portlets = new portlets('related_mail_' . $this->entities_id, $is_collapsed);
        
        //set mail_to
        $from_email = $item_info['field_' . $this->accounts_entities['from_email']]??'';
        $mail_to = strlen($from_email) ? '&mail_to=' . str_replace("\n",',',$from_email) : '';
        
        //build html block
        $html = '
		<div class="portlet portlet-related-items">
                    <div class="portlet-title">
			<div class="caption">        
                            ' . TEXT_EXT_RELATED_EMAILS . ' (' . $count_mails . ')              
                        </div>
                        
                        <div class="tools">                            
                            <a href="javascript:;" class="' . $portlets->button_css(). '"></a>
			</div>
                        
                        <div class="buttons">
                            <a href="javascript: open_dialog(\'' . url_for('ext/mail/create','redirect_to=item_info_mail_' . $this->position . '&path=' . $app_path . $mail_to) . '\')"><i class="fa fa-plus"></i></a> 
                        </div>
        
                    </div>
                    <div class="portlet-body" ' . $portlets->render_body() . '>';
        
        $html_table = '
         <table class="table ">';

        $related_contacts = [];                
        while($related_mails = db_fetch_array($related_mails_query))
        {        
            $mail_date = self::render_mail_date($related_mails['date_added']);
            
            $html_table .= '
                    <tr class="' . ($related_mails['is_new']>0 ? 'new-email':'') . '">
                        <td>
                            <a href="' . url_for('ext/mail/info', 'id=' . $related_mails['id']) . '"><i class="fa fa-envelope-o" aria-hidden="true"></i> ' . htmlspecialchars($related_mails['subject_cropped']) . ($related_mails['count_mails']>1 ? ' (' . $related_mails['count_mails'] . ')':'') . '</a>
                        </td>
                        <td align="right">
                            ' . ($related_mails['is_new']>0 ? '<span class="label label-warning">' . $mail_date . '</span>' : $mail_date). '
                        </td>
                    </tr>';           

            if($this->accounts_entities['bind_to_sender'] == 1)
            {
                $mail_info_query = db_query("select from_email, from_name from app_ext_mail where groups_id='" . $related_mails['id'] . "' order by id asc");
                if($mail_info = db_fetch_array($mail_info_query))
                {

                    $count_query = db_query("select count(*) as total from app_ext_mail where from_email='" . $mail_info['from_email'] . "' and in_trash=0");
                    $count = db_fetch_array($count_query);

                    $related_contacts[$mail_info['from_email']] = [
                        'from_email' => $mail_info['from_email'],
                        'from_name' => $mail_info['from_name'],
                        'count_mails' => $count['total']
                    ];
                }
            }
        }

        if(count($related_contacts))
        {
            $html_table .= '
                <tr>
                    <td style="padding-top: 15px;">' . TEXT_EXT_RELATED_CONTACTS . '</td>
                </tr>';

            foreach($related_contacts as $contact)
            {
                $html_table .= '
                    <tr>
                            <td><a href="' . url_for('ext/mail/accounts', 'search=' . $contact['from_email']) . '">' . $contact['from_name'] . ' (' . $contact['count_mails'] . ')</a></td>
                    </tr>';
            }
        }
        
        $html_table .='</table>';
        
        
        if($count_mails)
        {
            $html .= $html_table;
        }
        else
        {
            $html .= TEXT_NO_RECORDS_FOUND;
        }

        $html .= '                        
                </div>
            </div>
            ';

        
        return $html;
    }
    
    static function render_mail_date($date_added)
    {
        
        if(date('Y-m-d')==date('Y-m-d',$date_added))
        {
            $date = date('H:i',$date_added);
        }
        elseif(date('Y')==date('Y',$date_added))
        {
            $date = i18n_date('M d',$date_added);
        }
        else
        {
            $date = i18n_date(CFG_APP_DATE_FORMAT,$date_added);
        }
        
        return $date;
    }
    
    static function link_item_to_mail($mail_data,$path)
    {
        $path_info = items::parse_path($path);
        
        $entity_id = $path_info['entity_id'];
        $item_id = $path_info['item_id'];
                
        $accounts_entities_query = db_query("select ae.* from app_ext_mail_accounts_entities ae where ae.entities_id='" . $entity_id  . "' and accounts_id ='" . $mail_data['accounts_id'] . "'");
        if($accounts_entities = db_fetch_array($accounts_entities_query))
        {
            $sql_data = [
		'mail_groups_id' => $mail_data['groups_id'],
		'entities_id' => $entity_id,
		'items_id' => $item_id,
		'from_email' => $mail_data['from_email'],
            ];

            db_perform('app_ext_mail_to_items',$sql_data);
        }
    }
    
    static function delete_entity_by_id($entity_id)
    {
        $accounts_query = db_query("select * from app_ext_mail_accounts_entities where entities_id={$entity_id}");
        while($accounts = db_fetch_array($accounts_query))
        {
            db_delete_row('app_ext_mail_accounts_entities',$accounts['id']);
            db_query("delete from app_ext_mail_accounts_entities_fields where account_entities_id='" . db_input($accounts['id']) . "'");
            db_query("delete from app_ext_mail_accounts_entities_filters where account_entities_id='" . db_input($accounts['id']) . "'");
            db_query("delete from app_ext_mail_accounts_entities_rules where account_entities_id='" . db_input($accounts['id']) . "'");
        }
    }

}
