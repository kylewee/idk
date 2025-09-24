<?php

/* 
 *  Этот файл является частью программы "CRM Руководитель" - конструктор CRM систем для бизнеса
 *  https://www.rukovoditel.net.ru/
 *  
 *  CRM Руководитель - это свободное программное обеспечение, 
 *  распространяемое на условиях GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 *  
 *  Автор и правообладатель программы: Харчишина Ольга Александровна (RU), Харчишин Сергей Васильевич (RU).
 *  Государственная регистрация программы для ЭВМ: 2023664624
 *  https://fips.ru/EGD/3b18c104-1db7-4f2d-83fb-2d38e1474ca3
 */


$audioRecordingScript = url_for('ext/app_chat/audiorecording','assigned_to=' . $assigned_to . '&is_conversation=' . $is_conversation . '&attachments_form_token=' . $attachments_form_token);
$audioUploadScript  = url_for('ext/app_chat/audiorecording','assigned_to=' . $assigned_to . '&is_conversation=' . $is_conversation . '&action=upload&attachments_form_token=' . $attachments_form_token);

echo '<a href="' . $audioRecordingScript . '" audioUploadScript="' . $audioUploadScript . '" class="btn btn-default btn-microphone btn-microphone-chat"><i class="fa fa-microphone"></i></a>';
?>
<script>
$(".btn-microphone-chat").fancybox({
        type: "ajax",
        helpers: {
                overlay : {
                    closeClick: false
                }
            },
        beforeClose:function(){
            audiorecorder_form.resetRecordingTimer()
        }    
})
 
</script>