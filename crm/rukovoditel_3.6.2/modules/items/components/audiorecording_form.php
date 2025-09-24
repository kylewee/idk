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


if($_GET['field_id']=='attachments')
{
    $entity_cfg = new entities_cfg($current_entity_id);
    if($entity_cfg->get('comments_allow_audio_recording',0)==0)
    {
        exit();
    }
    
    $field = ['id'=>'attachments'];
    
    $cfg = new settings(['audio_recording_length'=>$entity_cfg->get('comments_audio_recording_length')]);
}
else
{
    $field = db_find('app_fields',_GET('field_id'));
    $cfg = new settings($field['configuration']);

    if($cfg->get('allow_audio_recording')!=1)
    {
        exit();
    }
}

?>

   
<div class="audiorecording-form" attachment_field_id="<?= $field['id'] ?>">      
    <div id="timer">        
         <span id="timerHours" class="hidden">00:</span><span id="timerMinutes">00:</span><span id="timerSeconds">00</span>  
    </div>

    <div id="range_slider" audio_recording_length="<?= $cfg->get('audio_recording_length') ?>">
        <input type="text" class="audiorecording-slider" name="my_range" value="" data-from-fixed="true"/>
    </div>

    <div id="controls">
        <button id="recordButton" class="btn btn-danger btn-record"><i class="fa fa-microphone"></i> <?= TEXT_START_RECORDING ?></button>
        <button id="stopButton"  class="btn btn-default btn-stop hidden"><i class="fa fa-stop"></i> <?= TEXT_STOP ?></button>
        <button id="pauseButton"  class="btn btn-default btn-pause hidden"><i class="fa fa-pause"></i> <?= TEXT_PAUSE ?></button>
        <button id="resumeButton"  class="btn btn-default btn-pause hidden"><i class="fa fa-play"></i> <?= TEXT_RESUME ?></button>        
    </div>
    <!--div id="formats">Format: start recording to see sample rate</div--> 

    <ol id="recordingsList">        
    </ol>

    <center>
        <button id="recordSave" class="btn btn-primary hidden"><?= TEXT_SAVE ?></button>
    </center>        
</div> 

<script>

var audiorecorder_form = new audiorecorder_helper()

function audiorecorderCreateDownloadLink(blob)
{
    audiorecorder_form.createDownloadLink(blob)
}

$('#recordSave').click(function(){
    //console.log(audio_data)    
    var audioUploadScript = $('.btn-microphone-<?= $field['id'] ?>').attr('audioUploadScript')
        
    var fd = new FormData();
                
    audiorecorder_form.audio_data.forEach((value,key)=>{
        fd.append("audio_data[]", value, audiorecorder_form.audio_data_time.get(key));
        //console.log(value)                
    })
                
    var xhr = new XMLHttpRequest();
    xhr.onload = function (e)
    {
        if (this.readyState === 4 && this.status == 200)
        {
            //console.log("Server returned: ", e.target.responseText);
            uploadifive_oncomplate_filed_<?= $field['id'] ?>()
            $.fancybox.close()
        }
    };
    
    xhr.open("POST", audioUploadScript,true);
    xhr.send(fd);
            
})
</script>
