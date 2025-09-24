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

<h3 class="page-title"><?php echo TEXT_MENU_USERS_REGISTRATION ?></h3>

<?php echo form_tag('cfg', url_for('configuration/save', 'redirect_to=configuration/users_registration'), array('class' => 'form-horizontal')) ?>
<div class="form-body">


    <div class="tabbable tabbable-custom">

        <ul class="nav nav-tabs">
            <li class="active"><a href="#user_registration"  data-toggle="tab"><?php echo TEXT_MENU_USER_REGISTRATION_EMAIL ?></a></li>     
        </ul>


        <div class="tab-content">
            <div class="tab-pane fade active in" id="user_registration">	

                <div class="form-group">
                    <label class="col-md-3 control-label" for="CFG_REGISTRATION_EMAIL_SUBJECT"><?php echo TEXT_REGISTRATION_EMAIL_SUBJECT ?></label>
                    <div class="col-md-9">	
                        <?php echo input_tag('CFG[REGISTRATION_EMAIL_SUBJECT]', CFG_REGISTRATION_EMAIL_SUBJECT, array('class' => 'form-control input-xlarge')); ?>
                        <span class="help-block"><?php echo TEXT_EXAMPLE . ': ' . TEXT_NEW_USER_DEFAULT_EMAIL_SUBJECT ?></span>
                    </div>			
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="CFG_REGISTRATION_EMAIL_BODY"><?php echo TEXT_REGISTRATION_EMAIL_BODY ?></label>
                    <div class="col-md-9">	
                        <?php echo textarea_tag('CFG[REGISTRATION_EMAIL_BODY]', CFG_REGISTRATION_EMAIL_BODY, array('class' => 'form-control input-xlarge editor')); ?>
                        <span class="help-block"><?php echo TEXT_REGISTRATION_EMAIL_BODY_NOTE ?></span>
                        <?php echo tooltip_text(TEXT_EXAMPLE . ': [FirstName] [LastName] [password]') ?>
                    </div>			
                </div>

            </div>

        </div>

    </div>  


    <?php echo submit_tag(TEXT_BUTTON_SAVE) ?>

</div> 
</form>