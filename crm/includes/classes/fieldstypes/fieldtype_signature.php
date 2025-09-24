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

class fieldtype_signature
{

    public $options;

    function __construct()
    {
        $this->options = array('title' => TEXT_FIELDTYPE_SIGNATURE_TITLE);
    }

    function get_configuration($params = array())
    {
        $choices = [
            '1' => TEXT_YES,
            '2' => TEXT_YES  . ' (' . TEXT_CURRENT_USER. ')',
            '0' => TEXT_NO,
        ];
        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_DISPLAY_INPUT_FIELD_ENTER_PERSON_NAME, 'name' => 'display_person_name_input', 'type' => 'dropdown','choices'=>$choices, 'params'=>['class'=>'form-control input-medium']);
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_DISPLAY_USER_CURRENT_SIGNATURE, 'name' => 'use_current_signature', 'type' => 'dropdown','tooltip_icon'=>TEXT_DISPLAY_USER_CURRENT_SIGNATURE_TIP,'choices'=>[0=>TEXT_NO, 1=>TEXT_YES], 'params'=>['class'=>'form-control input-small']);
        
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_DESCRIPTION, 'name' => 'signature_description', 'type' => 'textarea', 'params' => array('class' => 'form-control textarea-small'));
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_WIDTH_IN_ITEM_PAGE, 'name' => 'signature_width_item_page', 'type' => 'input', 'params' => array('class' => 'form-control input-medium'), 'tooltip_icon' => TEXT_WIDTH_IN_ITEM_PAGE_INFO);
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_WIDTH_IN_PRINT_PAGE, 'name' => 'signature_width_print_page', 'type' => 'input', 'params' => array('class' => 'form-control input-medium'), 'tooltip_icon' => TEXT_WIDTH_IN_PRINT_PAGE_INFO);
        $cfg[TEXT_SETTINGS][] = array('title' => TEXT_HIDE_NAME_ON_PRINT_PAGE, 'name' => 'hide_name_on_print_page', 'type' => 'checkbox');
        

        $cfg[TEXT_BUTTON][] =['type'=>'html', 'html' => TEXT_FIELDTYPE_SIGNATURE_FILTERS_TIP];
        $cfg[TEXT_BUTTON][] = array('title' => TEXT_BUTTON_TITLE, 'name' => 'button_title', 'type' => 'input', 'params' => array('class' => 'form-control input-medium'), 'tooltip_icon' => TEXT_DEFAULT . ': ' . TEXT_APPROVE);
        $cfg[TEXT_BUTTON][] = array('title' => TEXT_ICON, 'name' => 'button_icon', 'type' => 'input_icon', 'params' => array('class' => 'form-control input-medium'));
        $cfg[TEXT_BUTTON][] = array('title' => TEXT_COLOR, 'name' => 'button_color', 'type' => 'colorpicker');
        
        $cfg[TEXT_BUTTON][] = array('title' => TEXT_DELETE_BTN,  'type' => 'section');
        $cfg[TEXT_BUTTON][] = array('title' => TEXT_USE_DELETE_BUTTON, 'name' => 'use_delete_button', 'tooltip_icon'=> TEXT_FIELDTYPE_USERS_APPROVE_CANCEL_BTN_TIP, 'type' => 'dropdown', 'choices' => ['0' => TEXT_NO, 'any_users' => TEXT_ANY_USERS,'creator'=>TEXT_DELETE_BY_CREATOR_ONLY], 'params' => array('class' => 'form-control input-large'));
        
        $choices = ['' => ''];

        $fields_query = db_query("select * from app_fields where type in ('fieldtype_stages','fieldtype_dropdown','fieldtype_radioboxes','fieldtype_dropdown_multiple','fieldtype_tags','fieldtype_checkboxes','fieldtype_autostatus') and entities_id='" . db_input($_POST['entities_id']) . "'");
        while($fields = db_fetch_array($fields_query))
        {
            $choices[$fields['id']] = $fields['name'];
        }

        $cfg[TEXT_BUTTON][] = array('title' => TEXT_HIDE_BUTTON,
            'name' => 'disable_cancel_btn_by_field',
            'type' => 'dropdown',
            'choices' => $choices,
            'tooltip_icon' => TEXT_FIELDTYPE_USERS_APPROVE_HIDE_CANCEL_BTN_TIP,
            'params' => array('class' => 'form-control input-large', 'onChange' => 'fields_types_ajax_configuration(\'disable_cancel_btn_by_field_values\',this.value)'),
        );
        
        $cfg[TEXT_BUTTON][] = array('name' => 'disable_cancel_btn_by_field_values', 'type' => 'ajax', 'html' => '<script>fields_types_ajax_configuration(\'disable_cancel_btn_by_field_values\',$("#fields_configuration_disable_cancel_btn_by_field").val())</script>');

        $cfg[TEXT_ACTION][] = array('title' => TEXT_ADD_COMMENT, 'name' => 'add_comment', 'type' => 'dropdown', 'choices' => ['0' => TEXT_NO, '1' => TEXT_YES], 'params' => array('class' => 'form-control input-small'));
        $cfg[TEXT_ACTION][] = array('title' => TEXT_COMMENT_TEXT, 'name' => 'comment_text', 'type' => 'textarea', 'params' => array('class' => 'form-control textarea-small'), 'tooltip_icon' => TEXT_DEFAULT . ': ' . TEXT_APPROVED);


        $choices = [];
        $choices[0] = '';

        if(is_ext_installed())
        {
            $processes_query = db_query("select id, name from app_ext_processes where entities_id='" . $params['entities_id'] . "' order by sort_order, name");
            while($processes = db_fetch_array($processes_query))
            {
                $choices[$processes['id']] = $processes['name'];
            }
        }

        $cfg[TEXT_ACTION][] = array('title' => TEXT_ALL_USERS_APPROVED, 'name' => 'run_process', 'type' => 'dropdown', 'choices' => $choices, 'params' => array('class' => 'form-control input-large'), 'tooltip' => TEXT_ALL_USERS_APPROVED_INFO);

        return $cfg;
    }
    
    function get_ajax_configuration($name, $value)
    {
        $cfg = array();

        switch($name)
        {
            case 'disable_cancel_btn_by_field_values':
                if(strlen($value))
                {
                    $field_query = db_query("select id, name, configuration from app_fields where id='" . $value . "'");
                    if($field = db_fetch_array($field_query))
                    {
                        $field_cfg = new fields_types_cfg($field['configuration']);

                        if($field_cfg->get('use_global_list') > 0)
                        {
                            $choices = global_lists::get_choices($field_cfg->get('use_global_list'), false);
                        }
                        else
                        {
                            $choices = fields_choices::get_choices($field['id'], false);
                        }

                        $cfg[] = array(
                            'title' => $field['name'],
                            'name' => 'disable_cancel_btn_by_field_choices',
                            'type' => 'dropdown',
                            'choices' => $choices,
                            'params' => array('class' => 'form-control input-large chosen-select', 'multiple' => 'multiple'),
                        );
                    }
                }
                break;
        }

        return $cfg;
    }

    function render($field, $obj, $params = array())
    {
        return false;
    }

    function process($options)
    {     
        return $options['current_field_value'];;
    }

    function output($options)
    {
        global $app_users_cache, $app_user, $app_path, $app_module_path;


        $cfg = new fields_types_cfg($options['field']['configuration']);

        //print_rr($options);

        if(isset($options['is_print']))
        {
            $html = '';

            if(strlen($options['value']))
            {
                if($cfg->get('hide_name_on_print_page')!=1)
                {
                    $html .= '<td>' . $options['value'] . '</td>';
                }

                $approved_users = approved_items::get_approved_users_by_field($options['field']['entities_id'], $options['item']['id'], $options['field']['id']);

                if(count($approved_users))
                {

                    $approved_users = current($approved_users);

                    if(strlen($approved_users['signature']))
                    {
                        $html .= '<td><img src="' . $approved_users['signature'] . '" width="' . (strlen($cfg->get('signature_width_print_page')) ? (int) $cfg->get('signature_width_print_page') : 150) . '"></td>';
                    }
                }
            }

            if(strlen($html))
            {
                $html = '
      			<table border="0">
      				<tr>' . $html . '</tr>
      			</table>
      			';
            }

            return $html;
        }
        elseif(isset($options['is_export']) or isset($options['is_email']) or isset($options['is_comments_listing']))
        {
            return $options['value'];
        }
        else
        {
            $html = '';

            if(!strlen($options['value']) and $this->check_button_filter($options))
            {

                $button_title = (strlen($cfg->get('button_icon')) ? app_render_icon($cfg->get('button_icon')) . ' ' : '') . (strlen($cfg->get('button_title')) ? $cfg->get('button_title') : TEXT_APPROVE);

                $btn_css = 'btn-color-' . $options['field']['id'];

                $path_info = items::get_path_info($options['field']['entities_id'], $options['item']['id'], $options['item']);


                $redirect_to = '&redirect_to=items';

                if(isset($options['redirect_to']))
                {
                    if(strlen($options['redirect_to']) > 0)
                    {
                        $redirect_to = '&redirect_to=' . $options['redirect_to'];
                    }
                }
                elseif($app_module_path == 'items/info')
                {
                    $redirect_to = '&redirect_to=items_info';
                }

                //print_rr($options);					

                $redirect_to .= (isset($_POST['page']) ? '&gotopage[' . $options['reports_id'] . ']=' . $_POST['page'] : '');

                $button_html = button_tag($button_title, url_for('items/signature_field', 'fields_id=' . $options['field']['id'] . '&path=' . $path_info['full_path'] . $redirect_to), true, ['class' => 'btn btn-primary btn-sm ' . $btn_css]);

                $html .= '<div style="padding-top: 5px;">' . $button_html . app_button_color_css($cfg->get('button_color'), $btn_css) . '</div>';
            }
            elseif(strlen($options['value']))
            {
                $html .= '<div id="signature_info_' . $options['field']['id'] . '_' . $options['item']['id'] . '">';
                $approved_users = approved_items::get_approved_users_by_field($options['field']['entities_id'], $options['item']['id'], $options['field']['id']);

                if(count($approved_users))
                {
                    $approved_users_id = key($approved_users);
                    $approved_users = current($approved_users);

                    if(strlen($approved_users['signature']))
                    {
                        $html .= '<img src="' . $approved_users['signature'] . '" width="' . (strlen($cfg->get('signature_width_item_page')) ? (int) $cfg->get('signature_width_item_page') : 150) . '">';
                    }
                
                    //show delete button
                    if(!isset($options['is_listing']))
                    {
                        $use_delete_btn = true;

                        if($cfg->get('use_delete_button')=='0')
                        {
                            $use_delete_btn = false;
                        }
                                                
                        if($cfg->get('use_delete_button')=='creator' and $app_user['id']!=$approved_users_id)
                        {
                            $use_delete_btn = false;
                        }
                        
                        if(strlen($cfg->get('disable_cancel_btn_by_field')))
                        {
                            if(isset($options['item']['field_' . $cfg->get('disable_cancel_btn_by_field')]))
                            {
                                if(is_array($cfg->get('disable_cancel_btn_by_field_choices')))
                                    foreach($cfg->get('disable_cancel_btn_by_field_choices') as $choices_id)
                                    {
                                        if(in_array($choices_id, explode(',', $options['item']['field_' . $cfg->get('disable_cancel_btn_by_field')])))
                                        {
                                            $use_delete_btn = false;
                                        }
                                    }
                            }
                        }
                        
                        if(!$this->check_button_filter($options))
                        {
                            $use_delete_btn = false;
                        }

                        if($use_delete_btn)
                        {
                            $html .= '<div> ' . $options['value'] . ' <a href="javascript: remove_signature_' . $options['field']['id'] . '_' . $options['item']['id'] . '()" title="' . TEXT_DELETE . '"><i class="fa fa-trash-o"></i></a></div>';
                        }
                        else
                        {
                            $html .= '<div> ' . $options['value'] . '<div>';
                        }
                    }
                }

                $html .= '
                </div>

                <script>
                        function remove_signature_' . $options['field']['id'] . '_' . $options['item']['id'] . '()
                        {
                            if(confirm("' . addslashes(TEXT_ARE_YOU_SURE) . '"))
                            {		
                                $("#signature_info_' . $options['field']['id'] . '_' . $options['item']['id'] . '").hide();
                                $.ajax({method:"POST",url:"' . url_for('items/signature_field', 'action=cancel_singature&fields_id=' . $options['field']['id'] . '&path=' . $options['path']) . '"})
                            }
                        }
                </script>					
            ';
            }


            return $html;
        }
    }

    function check_button_filter($options)
    {
        global $sql_query_having;

        $field_id = $options['field']['id'];
        $entities_id = $options['field']['entities_id'];

        $reports_info_query = db_query("select * from app_reports where entities_id='" . db_input($entities_id) . "' and reports_type='fieldfilter" . $field_id . "'");
        if($reports_info = db_fetch_array($reports_info_query))
        {
            $reports_fileds = [];
            $filtes_query = db_query("select fields_id from app_reports_filters where reports_id='" . $reports_info['id'] . "'");
            while($filtes = db_fetch_array($filtes_query))
            {
                $reports_fileds[] = $filtes['fields_id'];
            }

            $listing_sql_query = "e.id='" . $options['item']['id'] . "'";
            $listing_sql_query_having = '';

            $listing_sql_select = fieldtype_formula::prepare_query_select($reports_info['entities_id'], '', false, ['fields_in_query' => implode(',', $reports_fileds)]);

            $listing_sql_query = reports::add_filters_query($reports_info['id'], $listing_sql_query);

            //prepare having query for formula fields
            if(isset($sql_query_having[$reports_info['entities_id']]))
            {
                $listing_sql_query_having = reports::prepare_filters_having_query($sql_query_having[$reports_info['entities_id']]);
            }

            $listing_sql = "select  e.* " . $listing_sql_select . " from app_entity_" . $reports_info['entities_id'] . " e where " . $listing_sql_query . $listing_sql_query_having;
            $items_query = db_query($listing_sql, false);
            if($item = db_fetch_array($items_query))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        return true;
    }

    function reports_query($options)
    {
        global $app_user;

        $filters = $options['filters'];
        $sql_query = $options['sql_query'];

        if(strlen($filters['filters_values']) > 0)
        {
            $filters['filters_values'] = str_replace('current_user_id', $app_user['id'], $filters['filters_values']);

            $sql_query[] = "(select count(*) from app_entity_" . $options['entities_id'] . "_values as cv where cv.items_id=e.id and cv.fields_id='" . db_input($options['filters']['fields_id']) . "' and cv.value in (" . $filters['filters_values'] . ")) " . ($filters['filters_condition'] == 'include' ? '>0' : '=0');
        }

        return $sql_query;
    }
    
    static function render_previous_signature($cfg, $fields_id)
    {
        global $app_user, $app_path, $app_redirect_to;
        
        if($cfg->get('use_current_signature')!=1) return '';
        
        $html = '';
        $check_query = db_query("select * from app_approved_items where users_id=" . $app_user['id'] . " and length(signature)>0 order by id desc", false);
        if($check = db_fetch_array($check_query))
        {
            $url = url_for('items/signature_field','action=singature&fields_id=' . $fields_id . '&path=' . $app_path . '&redirect_to=' . $app_redirect_to . '&use_signature=' . $check['id'] . (isset($_GET['gotopage']) ? '&gotopage[' . key($_GET['gotopage']). ']=' . current($_GET['gotopage']) : '') );
            $html = '
                <div class="media">
                    <div class="media-left media-middle">
                      <a href="' . $url . '">
                        <img class="media-object previous-signatur-img" src="' . $check['signature']  . '">
                      </a>
                    </div>
                    <div class="media-body">
                      <h4 class="media-heading">' . TEXT_MY_CURRENT_SIGNATURE . '</h4>
                      <a href="' . $url . '" class="btn btn-primary btn-sm"><i class="fa fa-angle-left"></i> ' . TEXT_USE_ID_FEM . '</a>
                    </div>
                </div>
                <hr>
                ';
        }
        
        return $html;
    }

}
