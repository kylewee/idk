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
    case 'get_pdf_file':
        header('Content-type: ' . $file['mime_type']);
        header('Content-Disposition: inline; filename="' . $file['name']. '"');
        echo file_get_contents($file['file_path']);
        exit();
        break;
}

if(is_mobile())
{     
    $html = '    
            <iframe id="text_preview" class="attachment-previw-doc' . (is_mobile() ? '-mobile':''). '" src="js/pdfViewerJS/#' . url_for('ext/mail/attachment_preview_pdf','action=get_pdf_file&id=' . _GET('id'). '&file=' . urlencode(base64_encode($file['file'])))  . '"></iframe>    
        ';
}
else
{

    $html = '    
            <iframe id="text_preview" class="attachment-previw-doc' . (is_mobile() ? '-mobile':''). '" src="' . url_for('ext/mail/attachment_preview_pdf','action=get_pdf_file&id=' . _GET('id'). '&file=' . urlencode(base64_encode($file['file'])))  . '"></iframe>    
        ';
}

$html .= '    
        <script>    
            if(is_mobile)
            {
                $("#text_preview").css("width",($(window).width()-100)+"px").css("height",($(window).height()-100)+"px")
            }
            else
            {
                $("#text_preview").css("height",($(window).height()-200)+"px")
            }
        </script>
    ';
echo $html;
