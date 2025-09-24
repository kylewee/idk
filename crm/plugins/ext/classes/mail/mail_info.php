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

class mail_info
{

    public $mail;

    function __construct($mail)
    {
        $this->mail = $mail;
    }

    function render_attachments_icon()
    {
        if (strlen($this->mail['attachments']))
        {
            return '<i class="fa fa-paperclip" aria-hidden="true"></i> ';
        }
        else
        {
            return '';
        }
    }

    function count_attachments()
    {
        if (strlen($this->mail['attachments']))
        {
            return count(explode(',', $this->mail['attachments']));
        }
        else
        {
            return 0;
        }
    }

    function render_mail_to()
    {
        $mail_to = [];

        $to_name = explode(',', $this->mail['to_name']);

        foreach (explode(',', $this->mail['to_email']) as $k => $mail)
        {
            $mail_to[] = (strlen($to_name[$k]) ? '<span title="' . $mail . '">' . $to_name[$k] . '</span>' : $mail);
        }

        return implode(', ', $mail_to);
    }

    static function render_mail_to_full($mail)
    {
        $mail_to = [];

        $to_name = explode(',', $mail['to_name']);

        foreach (explode(',', $mail['to_email']) as $k => $mail)
        {
            $mail_to[] = (strlen($to_name[$k]) ? $to_name[$k] . ' &lt;' . $mail . '&gt;' : $mail);
        }

        return implode(', ', $mail_to);
    }

    function render_headers()
    {
        $html = '';

        return $html;
    }

    function render_attachments()
    {
        if (!strlen($this->mail['attachments']))
            return '';

        $html = '
				<div class="email-attachments-heading"><b>' . TEXT_ATTACHMENTS . '</b> ' . ($this->count_attachments() > 1 ? '<a href="' . url_for('ext/mail/info', 'id=' . $this->mail['groups_id'] . '&mail_id=' . $this->mail['id'] . '&action=download_all_attachment') . '"><i class="fa fa-download" aria-hidden="true"></i> ' . TEXT_DOWNLOAD_ALL_ATTACHMENTS . '</a>' : '') . '</div>
        <div class="table-scrollable" style="margin-top: 5px !important;">
          <table class="table">
            <tbody>
              <tr>
                <td>
				
		    					<ul style="padding: 0px; margin: 0px;">';

        foreach (explode(',', $this->mail['attachments']) as $filename)
        {
            $file = self::parse_attachment_filename($filename);
            

            $class = '';
            switch (true)
            {
                case (is_google_doc($file['file_path']) and strlen(CFG_SERVICE_DOCX_PREVIEW)):
                    $link = link_to($file['name'], url_for('ext/mail/attachment_preview_doc', 'id=' . $this->mail['groups_id'] . '&file=' . urlencode(base64_encode($filename))), array('title'=>$file['name'],'target' => '_blank', 'class'=> (CFG_SERVICE_DOCX_PREVIEW!='docs.yandex.ru' ? 'fancybox-ajax':'')));
                    break;                
                case is_pdf($file['file_path']):
                    //$link = link_to($file['name'], url_for('ext/mail/attachment_preview_pdf', 'id=' . $this->mail['groups_id'] . '&file=' . urlencode(base64_encode($filename))), array('title'=>$file['name'],'target' => '_blank','class'=>'fancybox-ajax'));
                    
                    if(is_mobile())
                    {
                        $link = link_to($file['name'], url_for('ext/mail/info', 'id=' . $this->mail['groups_id'] . '&action=download_attachment&file=' . urlencode(base64_encode($filename))));
                    }
                    else
                    {
                        $link = link_to($file['name'], url_for('ext/mail/info', 'id=' . $this->mail['groups_id'] . '&action=download_attachment&preview=1&file=' . urlencode(base64_encode($filename))), ['target' => '_blank']);
                    }
                    break; 
                case is_text($file['file_path']):
                    $link = link_to($file['name'], url_for('ext/mail/attachment_preview_text', 'id=' . $this->mail['groups_id'] . '&file=' . urlencode(base64_encode($filename))), array('title'=>$file['name'],'target' => '_blank','class'=>'fancybox-ajax'));
                    break;                
                case is_video($file['file_path']):
                    $link = link_to($file['name'], url_for('ext/mail/attachment_preview_video', 'id=' . $this->mail['groups_id'] . '&file=' . urlencode(base64_encode($filename))), array('title'=>$file['name'],'target' => '_blank','class'=>'fancybox-ajax'));
                    break;
                case is_audio($file['file_path']):
                    $link = link_to($file['name'], url_for('ext/mail/attachment_preview_audio', 'id=' . $this->mail['groups_id'] . '&file=' . urlencode(base64_encode($filename))), array('title'=>$file['name'],'target' => '_blank','class'=>'fancybox-ajax'));
                    break;
                case is_image($file['file_path']):
                    $link = link_to($file['name'], url_for('ext/mail/info', 'id=' . $this->mail['groups_id'] . '&action=preview_attachment_image&file=' . urlencode(base64_encode($filename))), ['class' => 'fancybox-ajax', 'data-fancybox-group' => 'gallery']);
                    break;                
                default:
                    $link = link_to($file['name'], url_for('ext/mail/info', 'id=' . $this->mail['groups_id'] . '&action=download_attachment&file=' . urlencode(base64_encode($filename))));
                    break;
            }



            $link .= ' ' . link_to('<i class="fa fa-download"></i>', url_for('ext/mail/info', 'id=' . $this->mail['groups_id'] . '&action=download_attachment&file=' . urlencode(base64_encode($filename))));

            $link .= ' <small>(' . $file['size'] . ')</small>';

            $html .= '
		              <li style="list-style-image: url(' . url_for_file($file['icon']) . '); margin-left: 20px;">' . $link . '</li>
		            ';
        }

        $html .= '
		    					</ul>
				
								</td>
              </tr>
            </tbody>
          </table>
        </div>
				
						<script type="text/javascript">
            	$(document).ready(function() {
            		$(".fancybox").fancybox({type: "ajax"});
            	});
            </script>
            				
				';

        return $html;
    }

    function render_date()
    {
        return format_date_time($this->mail['date_added'], CFG_MAIL_DATETIME_FORMAT) . $this->get_time_ago('@' . $this->mail['date_added']);
    }

    function get_time_ago($datetime)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $w = floor($diff->d / 7);
        $diff->d -= $w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
        );
        
        //print_rr($diff);
        
        $has_diff = false;

        foreach ($string as $k => &$v)
        {
            if ($diff->$k)
            {
                $floor_diff = $diff->$k - (floor($diff->$k / 10) * 10);

                switch ($k)
                {
                    case 'i':
                        $text_pattern = [TEXT_EXT_ONE_MINUTE_AGO, TEXT_EXT_MINUTES_AGO, TEXT_EXT_MINUTES_AGO_LONG];
                        break;
                    case 'h':
                        $text_pattern = [TEXT_EXT_ONE_HOUR_AGO, TEXT_EXT_HOURS_AGO, TEXT_EXT_HOURS_AGO_LONG];
                        break;
                    case 'd':
                        $text_pattern = [TEXT_EXT_ONE_DAY_AGO, TEXT_EXT_DAYS_AGO, TEXT_EXT_DAYS_AGO_LONG];
                        break;
                    case 'm':                        
                        $text_pattern = [TEXT_EXT_ONE_MONTH_AGO, TEXT_EXT_MONTHS_AGO, TEXT_EXT_MONTHS_AGO_LONG];
                        break;
                    case 'y':                        
                        $text_pattern = [TEXT_EXT_ONE_YEAR_AGO, TEXT_EXT_YEARS_AGO, TEXT_EXT_YEARS_AGO_LONG];
                        break;
                }

                $v = $diff->$k . ' - ' . (($floor_diff == 1 and $diff->$k != 11) ? $text_pattern[0] : (($floor_diff > 1 and $floor_diff < 5 and!in_array($diff->$k, [11, 12, 13, 14])) ? $text_pattern[1] : $text_pattern[2]));                                
                
                $has_diff = true;
            }
            else
            {
                unset($string[$k]);
            }
        }
                
        if ($has_diff)
        {
            $string = array_slice($string, 0, 1);
            return ' <span class="email-time-ago">(' . ($string ? implode(', ', $string) : '1 ' . TEXT_EXT_ONE_MINUTE_AGO) . ')</span>';
        }
        else
        {
            return '';
        }
    }

    static function crop_subject($subject)
    {
        $xtra = "|RE\[\d+\]|FW\[\d+\]|FYI\[\d+\]|RIF\[\d+\]|I\[\d+\]|FS\[\d+\]|VB\[\d+\]|RV\[\d+\]|ENC\[\d+\]|ODP\[\d+\]|PD\[\d+\]|YNT\[\d+\]|ILT\[\d+\]|SV\[\d+\]|VS\[\d+\]|VL\[\d+\]|AW\[\d+\]|WG\[\d+\]|ΑΠ\[\d+\]|ΣΧΕΤ\[\d+\]|ΠΡΘ\[\d+\]|תגובה\[\d+\]|הועבר\[\d+\]|主题|转发\[\d+\]|FWD\[\d+\]";
        $subject = preg_replace("/([\[\(] *)?(RE?S?|FW" . $xtra . "|FYI|RIF|I|FS|VB|RV|ENC|ODP|PD|YNT|ILT|SV|VS|VL|AW|WG|ΑΠ|ΣΧΕΤ|ΠΡΘ|תגובה|הועבר|主题|转发|FWD?) *([-:;)\]][ :;\])-]*|$)|\]+ *$/im", '', $subject);
        return trim($subject);
    }

    public static function prepare_attachment_filename($filename)
    {
        $filename = str_replace(array(" ", ","), "_", trim($filename));

        if (!is_dir(DIR_WS_MAIL_ATTACHMENTS . date('Y')))
        {
            mkdir(DIR_WS_MAIL_ATTACHMENTS . date('Y'));
        }

        if (!is_dir(DIR_WS_MAIL_ATTACHMENTS . date('Y') . '/' . date('m')))
        {
            mkdir(DIR_WS_MAIL_ATTACHMENTS . date('Y') . '/' . date('m'));
        }

        if (!is_dir(DIR_WS_MAIL_ATTACHMENTS . date('Y') . '/' . date('m') . '/' . date('d')))
        {
            mkdir(DIR_WS_MAIL_ATTACHMENTS . date('Y') . '/' . date('m') . '/' . date('d'));
        }

        return array('name' => time() . '_' . $filename,
            'file' => (CFG_ENCRYPT_FILE_NAME == 1 ? sha1(time() . '_' . $filename) : time() . '_' . $filename),
            'folder' => date('Y') . '/' . date('m') . '/' . date('d'));
    }

    static function parse_attachment_filename($filename)
    {
        $filename_array = explode('_', $filename);
        $filetime = (int) $filename_array[0];

        //get foler
        $folder = date('Y', $filetime) . '/' . date('m', $filetime) . '/' . date('d', $filetime);

        if (is_file(DIR_WS_MAIL_ATTACHMENTS . $folder . '/' . sha1($filename)))
        {
            $file_path = DIR_WS_MAIL_ATTACHMENTS . $folder . '/' . sha1($filename);
        }
        else
        {
            $file_path = DIR_WS_MAIL_ATTACHMENTS . $folder . '/' . $filename;
        }

        //get extension
        $filename_array = explode('.', $filename);
        $extension = strtolower($filename_array[sizeof($filename_array) - 1]);

        if (is_file('images/fileicons/' . $extension . '.png'))
        {
            $icon = 'images/fileicons/' . $extension . '.png';            
        }
        else
        {
            $icon = 'images/fileicons/attachment.png';
        }
        
        if(is_file($file_path))
        {
            $size = attachments::file_size_convert(filesize($file_path));
        }
        else
        {
            $size = 0;
        }

        return [
            'file' => $filename,
            'file_path' => $file_path,
            'name' => substr($filename, strpos($filename, '_') + 1),
            'size' => $size,
            'icon' => $icon,
            'mime_type'=> ($size ? mime_content_type($file_path) : ''),
        ];
    }

    static function render_attachments_preview($form_token)
    {
        $attachments_list = array();
        $attachments_id_list = array();

        $attachments_query = db_query("select * from app_attachments where form_token='" . db_input($form_token) . "' and container=0");
        while ($attachments = db_fetch_array($attachments_query))
        {
            $attachments_list[$attachments['id']] = $attachments['filename'];
            $attachments_id_list[] = $attachments['id'];
        }

        $html = '';

        if (count($attachments_list) > 0)
        {
            $html = '<table class="chat-attachments-table">';
            foreach ($attachments_list as $attachments_id => $v)
            {
                $file = mail_info::parse_attachment_filename($v);

                $html .= '
						<tr class="attachment-row-' . $attachments_id . '">
							<td><img src="' . url_for_file($file['icon']) . '"></td>
							<td>
									' . $file['name'] . '
									&nbsp;<small>(' . $file['size'] . ')</small>
									&nbsp;<a href="javascript: mail_attachment_remove(\'' . $attachments_id . '\')" class="chat-attachment-remove"><i class="fa fa-times" aria-hidden="true"></i></a>
							</td>
						</tr>';
            }
            $html .= '</table>';

            $html .= input_hidden_tag('message_attachments', implode(',', $attachments_list));
            $html .= input_hidden_tag('message_attachments_ids', implode(',', $attachments_id_list));
        }
        else
        {
            $html .= input_hidden_tag('message_attachments', '');
        }

        return $html;
    }

    static function delete_by_group_id($groups_id)
    {
        //delete attachments
        $mail_query = db_query("select attachments from app_ext_mail where in_trash=1 and groups_id='" . $groups_id . "' and length(attachments)>0");
        while ($mail = db_fetch_array($mail_query))
        {
            foreach (explode(',', $mail['attachments']) as $filename)
            {
                $file = mail_info::parse_attachment_filename($filename);

                if (is_file($file['file_path']))
                {
                    unlink($file['file_path']);
                }
            }
        }

        //delete rows
        db_query("delete from app_ext_mail where in_trash=1 and groups_id='" . $groups_id . "'");

        mail_accounts::delete_mail_group_by_id($groups_id);
    }
    
    static function prepare_body_attachments($mail_info)
    {
        $html = $mail_info['body'];
                
        
        if(!strlen($html) or !strlen($mail_info['attachments'])) return $html;
        
        if(preg_match_all('/<img[^>]+>/i',$html, $result))
        {  	              
            foreach($result[0] as $element) 
            {  	
                preg_match('/src="([^"]*)"/i',$element, $src);
                $src = $src[1];
                
                preg_match('/alt="([^"]*)"/i',$element, $alt);
                $alt = $alt[1]??'';
                
                                                
                switch(true)
                {
                    case (substr($src,0,4)=='cid:'):
                        //echo $src . ' - ' . $alt;
                        foreach (explode(',', $mail_info['attachments']) as $filename)
                        {
                            $file = self::parse_attachment_filename($filename);
                            $img_url = url_for('ext/mail/info&id=' . $mail_info['groups_id']  ,'&action=download_attachment&preview=1&file=' . urlencode(base64_encode($filename)));
                                                        
                            if($file['name']==$alt or strstr($src,$file['name']))
                            {                                
                                $html = str_replace($src,$img_url,$html);
                            }
                                                       
                        }
                        
                        break;
                }
            }
        }
        
        return $html;
    }

}
