<script>
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

    function submit_comments_form()
    {
        if (CKEDITOR_holders["description"])
        {
            CKEDITOR_holders["description"].updateElement();
        }

        if ($('#uploadifive_attachments_list_attachments .attachments-form-list').length)
        {
            $('#comments_attachments').val('true');
        }

        is_valid = false;

        $("#comments_form .form-control").each(function ()
        {
            if ($(this).val() != '')
            {
                is_valid = true;
            }
        })

        $("#comments_form .select_checkboxes_tag input").each(function ()
        {

            if ($(this).prop("checked"))
            {
                is_valid = true;
            }
        })

        if (!is_valid)
        {
            var message = '<?php echo htmlspecialchars(TEXT_ERROR_COMMENTS_FORM_GENERAL, ENT_QUOTES) ?>';
            $("div#form-error-container").html('<div class="alert alert-danger">' + message + '</div>');
            $("div#form-error-container").show();
            $("div#form-error-container").delay(5000).fadeOut();
        }
        else
        {
            //replace submit button to Loading to stop double submit
            app_prepare_modal_action_loading($("#comments_form"))

            $("#comments_form").submit();
        }
    }

    $(function ()
    {
        $("#comments_form").validate({
            ignore: '', 
            errorClass: 'error',
            submitHandler: function(form)
            {
                <?php echo (strlen(trim($entity_cfg->get('javascript_comments_from_onsubmit'))) ? $app_global_vars->apply_to_text($entity_cfg->get('javascript_comments_from_onsubmit')) :'') ?>
                
                return true                        
            }
        });

        app_currency_converter('#comments_form');

        <?php echo (isset($_GET['description']) ? 'app_move_caret_to_end("description")' : '') ?>
                
        <?=  strlen(trim($entity_cfg->get('javascript_in_comments_from'))) ? $app_global_vars->apply_to_text($entity_cfg->get('javascript_in_comments_from')) :'' ?>
    });

</script>