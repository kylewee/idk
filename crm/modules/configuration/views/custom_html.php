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
<h3 class="page-title"><?php echo TEXT_CUSTOM_HTML ?></h3>

<p><?php echo TEXT_CUSTOM_HTML_INFO ?></p>

<?php echo form_tag('cfg', url_for('configuration/save', 'redirect_to=configuration/custom_html'), array('class' => 'form-horizontal')) ?>
<div class="form-body">

    <div class="form-group">
        <label class="col-md-3 control-label" ><?php echo htmlspecialchars(TEXT_ADD_CODE_END_OF_HEAD) ?></label>
        <div class="col-md-9">	
            <?php echo textarea_tag('CFG[CUSTOM_HTML_HEAD]', CFG_CUSTOM_HTML_HEAD, array('class' => 'form-control code-mirror')) ; ?>
        </div>			
    </div>
    
    <div class="form-group">
        <label class="col-md-3 control-label" ><?php echo htmlspecialchars(TEXT_ADD_CODE_BEFORE_BODY) ?></label>
        <div class="col-md-9">	
            <?php echo textarea_tag('CFG[CUSTOM_HTML_BODY]', CFG_CUSTOM_HTML_BODY, array('class' => 'form-control code-mirror')) ; ?>
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
              height: 300,
              lineWrapping: true,
              matchBrackets: true,
              theme: app_skin_dir=='Dark_Mode' ? 'darcula':'default',
              extraKeys: {
                         "F11": function(cm) {
                           cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                         },
                         "Esc": function(cm) {
                          if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                        },    		    
                      } 
          }).setSize(null, 300);

    })    
});
      
</script>