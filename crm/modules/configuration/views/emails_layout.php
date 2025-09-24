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

<h3 class="page-title"><?php echo TEXT_EMAILS_LAYOUT ?></h3>

<p><?php echo TEXT_EMAILS_LAYOUT_INFO ?></p>

<?php echo form_tag('cfg', url_for('configuration/save'), array('class' => 'form-horizontal')) ?>
<?php echo input_hidden_tag('redirect_to', 'configuration/emails_layout') ?>
<div class="form-body">
    
    
    <div class="form-group">
        <label class="col-md-2 control-label" ><?php echo TEXT_TOGGLE_ON ?></label>
        <div class="col-md-10">	
            <?php echo select_tag('CFG[USE_EMAIL_HTML_LAYOUT]', $default_selector, CFG_USE_EMAIL_HTML_LAYOUT, array('class' => 'form-control input-small')); ?> 
        </div>			
    </div>
    
    <div class="form-group">
        <label class="col-md-2 control-label" ><?php echo TEXT_CUSTOM_HTML ?></label>
        <div class="col-md-10">	
            <?php echo textarea_tag('CFG[EMAIL_HTML_LAYOUT]', CFG_EMAIL_HTML_LAYOUT, array('class' => 'form-control code-mirror')) ; ?>
        </div>			
    </div>
    
    <?php echo submit_tag(TEXT_BUTTON_SAVE) ?>
    
</div>
</form>


<?php echo app_include_codemirror(['css']) ?>

<script>

$(function(){
    $('.code-mirror').each(function(){
    var editor = CodeMirror.fromTextArea(document.getElementById($(this).attr('id')), {    	  
              lineNumbers: true,       
              autofocus:true,
              height: 600,
              lineWrapping: true,
              matchBrackets: true,
              extraKeys: {
                         "F11": function(cm) {
                           cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                         },
                         "Esc": function(cm) {
                          if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                        },    		    
                      } 
          }).setSize(null, 600);

    })   
})
      
</script>
