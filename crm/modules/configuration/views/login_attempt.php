<h3 class="page-title"><?php echo TEXT_LOGIN_ATTEMPT ?></h3>

<p><?php echo TEXT_LOGIN_ATTEMPT_INFO ?></p>

<?php echo form_tag('cfg', url_for('configuration/save','redirect_to=configuration/login_attempt'),array('class'=>'form-horizontal')) ?>
<div class="form-body">
    

    
    <div class="form-group">
        <label class="col-md-3 control-label"><?php echo TEXT_NUMBER_LOGIN_ATTEMPTS ?></label>
        <div class="col-md-9">	
            <?php echo input_tag('CFG[NUMBER_LOGIN_ATTEMPTS]', CFG_NUMBER_LOGIN_ATTEMPTS, array('class' => 'form-control input-small','type'=>'number','min'=>2,'max'=>10)); ?>           
            <?php echo tooltip_text(TEXT_NUMBER_LOGIN_ATTEMPTS_TIP) ?>
        </div>			
    </div>
    
    <div class="form-group">
        <label class="col-md-3 control-label"><?php echo TEXT_NUMBER_MINUTES ?></label>
        <div class="col-md-9">	
            <?php echo input_tag('CFG[NUMBER_MINUTES_IP_BLOCKED]', CFG_NUMBER_MINUTES_IP_BLOCKED, array('class' => 'form-control input-small','type'=>'number','min'=>2)); ?>           
            <?php echo tooltip_text(TEXT_NUMBER_MINUTES_IP_BLOCKED_TIP) ?>
        </div>			
    </div>
    
    <div class="form-group">
        <label class="col-md-3 control-label"><?php echo TEXT_TRUSTED_IP ?></label>
        <div class="col-md-9">	
            <?php echo textarea_tag('CFG[LOGIN_ATTEMPT_TRUSTED_IP]', CFG_LOGIN_ATTEMPT_TRUSTED_IP, array('class' => 'form-control input-xlarge')); ?>           
            <?php echo tooltip_text(TEXT_TRUSTED_IP_TIP) ?>
        </div>			
    </div>
    
    
    <?php echo submit_tag(TEXT_BUTTON_SAVE) ?>
 
</div>
</form>

<script>
    $(function ()
    {
        $('#cfg').validate({ignore:''});
    });
</script> 