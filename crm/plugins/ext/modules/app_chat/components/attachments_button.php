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


$attachments_timestamp = time();

$html = '
      
      <input style="cursor: pointer" type="file" name="uploadifive_attachments_upload" id="uploadifive_attachments_upload" />

      <script type="text/javascript">

      var is_file_uploading = null;

  		$(function() {
  			$("#uploadifive_attachments_upload").uploadifive({
  				"auto"             : true,
          "dnd"              : false,
          "buttonClass"      : "chat-btn-attachments",
          "buttonText"       : "<i class=\"fa fa-paperclip\"></i>",
  				"formData"         : {
  									   "timestamp" : ' . $attachments_timestamp . ',
  									   "token"     : "' .  $attachments_form_token . '"
  				                     },
  				"queueID"          : "uploadifive_queue_list",
          "fileSizeLimit" : "' . CFG_SERVER_UPLOAD_MAX_FILESIZE . 'MB",
  				"uploadScript"     : "' .  url_for('ext/app_chat/chat','action=attachments_upload',true)  . '",
          "onUpload"         :  function(filesToUpload){
            is_file_uploading = true;
          },
  				"onQueueComplete" : function(file, data) {
            is_file_uploading = null
            $(".uploadifive-queue-item.complete").fadeOut();
            $("#uploadifive_attachments_list").append("<div class=\"loading_data\"></div>");
            $("#uploadifive_attachments_list").load("' .  url_for('ext/app_chat/chat','action=attachments_preview&token=' . $attachments_form_token) . '");

          }
  			});

        $("button[type=submit]").bind("click",function(){
            if(is_file_uploading)
            {
              alert("' . TEXT_PLEASE_WAYIT_FILES_LOADING . '"); return false;
            }
          });

  		});
	</script>
    ';


echo $html;