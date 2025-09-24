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

<?php 
$title = ($call_history['direction']=='in' ?  TEXT_EXT_INCOMING_CALL : TEXT_EXT_OUTGOING_CALL) . ' - ' . format_date_time($call_history['date_added']);
echo ajax_modal_template_header($title);
?>

<?php echo form_tag('call_history_form', url_for('ext/call_history/play','action=save&id=' . $call_history['id']), array('class' => 'form-horizontal')) ?>
<div class="modal-body">
    <div class="form-body">
        
        <?php if(strlen($call_history['client_name'])): ?>
        <div class="form-group">
            <label class="col-md-3 control-label" ><?php echo TEXT_NAME ?></label>
            <div class="col-md-9">	
                <p class="form-control-static"><?php echo  $call_history['client_name'] ?></p>      
            </div>			
        </div>
        <?php endif ?>
        
        <div class="form-group">
            <label class="col-md-3 control-label" ><?php echo TEXT_PHONE ?></label>
            <div class="col-md-9">	
                <p class="form-control-static"><?php echo  $call_history['phone'] ?></p>      
            </div>			
        </div>
        
        <div class="form-group">           
            <div class="col-md-12">
                <?php 
                    if($call_history['module']=='mango_office')
                    {
                        echo '<a href="https://app.mango-office.ru/vpbx/queries/recording/issa/' . $call_history['recording'] . '/play/" target="_balnk" class="btn btn-default"><i class="fa fa-play-circle-o" aria-hidden="true"></i> ' . TEXT_PLAY_AUDIO_FILE . '</a>';
                    }
                    elseif($call_history['module']=='zadarma')
                    {
                       include_once 'plugins/ext/telephony_modules/zadarma/zadarma.php'; 
                       include_once 'plugins/ext/telephony_modules/zadarma/languages/russian.php'; 
                       $zadarma = new zadarma();
                       echo $zadarma->play_audio_file($call_history['recording']); 
                    }
                    elseif($call_history['module']=='novofon')
                    {
                       include_once 'plugins/ext/telephony_modules/novofon/novofon.php'; 
                       include_once 'plugins/ext/telephony_modules/novofon/languages/russian.php'; 
                       $novofon = new novofon();
                       echo $novofon->play_audio_file($call_history['recording']); 
                    }
                    else
                    {
                        echo '<audio id="call_history_recording" controls autoplay src="' . $call_history['recording'] . '" style="width:100%">';
                    }
                ?>
                        
            </div>			
        </div>
        
        <div class="form-group">           
            <div class="col-md-12">
                <?php echo textarea_tag('comments',$call_history['comments'],['class'=>'form-control','placeholder'=>TEXT_COMMENT]) ?>
            </div>			
        </div>
    </div>
</div> 

<?php echo ajax_modal_template_footer() ?>
</form>

<script>
$('#ajax-modal').on('hidden.bs.modal', function (e) {  
  let player = document.getElementById('call_history_recording');
  if(player)
  {
    player.pause();
  }
})

$('#call_history_form').submit(function(){
    app_prepare_modal_action_loading(this)
    $.ajax({
        type:'POST',
        url: $(this).attr('action'),
        data: $(this).serializeArray()
    }).done(function(){
        $('#ajax-modal').modal('toggle')
        load_items_listing('call_history_listing','<?php echo _GET('page') ?>');
    })
    
    return false;
})
</script>
    