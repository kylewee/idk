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

$email_info_query = db_query("select * from app_ext_mail where groups_id='" . _get::int('id') . "' and accounts_id in (select accounts_id from app_ext_mail_accounts_users where users_id='" . $app_user['id'] . "') order by date_added limit 1");
if(!$email_info = db_fetch_array($email_info_query))
{
	redirect_to('dashboard/access_forbidden');
}


$file = mail_info::parse_attachment_filename(base64_decode($_GET['file']));
    		  	
if(!is_file($file['file_path']))
{
    die(TEXT_FILE_NOT_FOUD);
}

switch($app_module_action)
{
    case 'get_video_file':
        header('Content-type: ' . $file['mime_type']);
        echo file_get_contents($file['file_path']);
        exit();
        break;
}


$html = '
    <div class="attachment-previw-window' . (is_mobile() ? '-mobile':''). '">
        <video id="player" playsinline controls autoplay  style="width:100%">
                <source src="' . url_for('ext/mail/attachment_preview_video','action=get_video_file&id=' . _GET('id'). '&file=' . urlencode(base64_encode($file['file']))) . '" type="' . $file['mime_type'] . '" />
        </video>
    </div>
    <script>
        document.getElementById("player").addEventListener("loadeddata", function() {
            if(this.videoHeight==0)
            {
                this.style.cssText="height: 60px; width: 100%";
            }
        })
    </script>
    ';


echo $html;
