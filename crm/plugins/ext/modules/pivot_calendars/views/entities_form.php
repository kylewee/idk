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

<?php echo ajax_modal_template_header(TEXT_EXT_СALENDAR_REPORTS) ?>

<?php echo form_tag('configuration_form', url_for('ext/pivot_calendars/entities','action=save&calendars_id=' . _get::int('calendars_id') . (isset($_GET['id']) ? '&id=' . $_GET['id']:'') ),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body ajax-modal-width-790">
     
        <ul class="nav nav-tabs">
            <li class="active"><a href="#general_info"  data-toggle="tab"><?php echo TEXT_GENERAL_INFO ?></a></li>              
            <li><a href="#reminder"  data-toggle="tab"><?php echo TEXT_EXT_REMINDER ?></a></li>    
        </ul>     
      
        <div class="tab-content">
            <div class="tab-pane fade active in" id="general_info">
                
                <div class="form-group">
                    <label class="col-md-3 control-label" for="type"><?php echo TEXT_REPORT_ENTITY ?></label>
                  <div class="col-md-9">	
                      <?php echo select_tag('entities_id',entities::get_choices(), $obj['entities_id'],array('class'=>'form-control input-large required','onChange'=>'ext_get_entities_fields(this.value)')) ?>        
                  </div>			
                </div>

                <div class="form-group">
                            <label class="col-md-3 control-label" for="bg_color"><?php echo TEXT_BACKGROUND_COLOR ?></label>
                        <div class="col-md-9">
                            <div class="input-group input-small color colorpicker-default" data-color="<?php echo (strlen($obj['bg_color'])>0 ? $obj['bg_color']:'#ff0000')?>" >
                               <?php echo input_tag('bg_color',$obj['bg_color'],array('class'=>'form-control input-small')) ?>
                            <span class="input-group-btn">
                                                    <button class="btn btn-default" type="button">&nbsp;</button>
                                            </span>
                                    </div>      
                        </div>			
                      </div>

                <div id="reports_entities_fields"></div>  
    
            </div> 
    
            <div class="tab-pane fade" id="reminder">
                <p><?php echo TEXT_EXT_CALENDAR_REMINDER_INFO ?></p>
                
                <div class="form-group">
                    <label class="col-md-3 control-label" for="default_view"><?php echo TEXT_STATUS ?></label>
                    <div class="col-md-9">	
                        <?php echo select_tag('reminder_status', ['0' => TEXT_TOGGLE_OFF, '1' => TEXT_TOGGLE_ON], $obj['reminder_status'], array('class' => 'form-control input-medium')) ?>        
                    </div>			
                </div>
                
                <div form_display_rules="reminder_status:1">
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="default_view"><?php echo TEXT_TYPE ?></label>
                        <div class="col-md-9">	
                            <?php echo select_tag('reminder_type[]', ['popup' => TEXT_EXT_POPUP, 'push' => TEXT_EXT_PUSH_REMINDER], $obj['reminder_type'], array('class' => 'form-control input-xlarge chosen-select','multiple'=>'multiple')) ?>        
                        </div>			
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="event_limit"><?php echo  TEXT_EXT_MINUTES ?></label>
                        <div class="col-md-9">	
                            <?php echo input_tag('reminder_minutes', $obj['reminder_minutes'], array('class' => 'form-control input-small','type'=>'number')) ?> 
                            <?= tooltip_text(TEXT_EXT_CALENDAR_REMINDER_MINUTES_INFO) ?>
                        </div>			
                    </div>                   
                    
                    <div id="reminder_item_heading_box"></div>
                    
                </div>                
            </div>
    
        </div>    
           
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>

  $(function() { 
    $('#configuration_form').validate();
                        
    ext_get_entities_fields($('#entities_id').val());
    
    ext_get_reminder_item_heading()
                                                                              
  });
  
function ext_get_reminder_item_heading()
{
    entities_id = $('#entities_id').val();
    $('#reminder_item_heading_box').load('<?php echo url_for("ext/pivot_calendars/entities", "action=get_reminder_item_heading&calendars_id=" . _GET('calendars_id')) ?>', {entities_id: entities_id, id: '<?php echo $obj["id"] ?>'},function()
    {
        appHandleUniform();
    })
}
  
function ext_get_entities_fields(entities_id)
{ 
  $('#reports_entities_fields').html('<div class="ajax-loading"></div>');
   
  $('#reports_entities_fields').load('<?php echo url_for("ext/pivot_calendars/entities","action=get_entities_fields&calendars_id=" . _get::int('calendars_id'))?>',{entities_id:entities_id, id:'<?php echo $obj["id"] ?>'},function(response, status, xhr) {
    if (status == "error") {                                 
       $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText+'<div>'+response +'</div></div>')                    
    }
    else
    {
      appHandleUniform();
    }    
  }); 
   
  
}   

  
</script>   