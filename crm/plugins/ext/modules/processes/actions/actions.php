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

$app_process_info_query = db_query("select * from app_ext_processes where id='" . _get::int('process_id') . "'");
if(!$app_process_info = db_fetch_array($app_process_info_query))
{
    redirect_to('ext/processes/processes');
}


$app_title = app_set_title(TEXT_EXT_PROCESSES_ACTIONS);

switch($app_module_action)
{
    case 'save':
        $sql_data = array(
            'process_id' => _get::int('process_id'),
            'is_active' => (isset($_POST['is_active']) ? 1 : 0),
            'type' => $_POST['type'],
            'description' => $_POST['description'],
            'sort_order' => $_POST['sort_order'],
            'settings' => (isset($_POST['settings']) ? json_encode($_POST['settings']) : ''),
        );

        if(isset($_GET['id']))
        {
            $actions_info = db_find('app_ext_processes_actions', $_GET['id']);

            //check type and if it's changed remove fileds action
            if($actions_info['type'] != $_POST['type'])
            {
                db_query("delete from app_ext_processes_actions_fields where actions_id='" . db_input($_GET['id']) . "'");
                db_query("delete from app_ext_processes_clone_subitems where actions_id='" . db_input($_GET['id']) . "'");
            }

            db_perform('app_ext_processes_actions', $sql_data, 'update', "id='" . db_input($_GET['id']) . "'");
        }
        else
        {
            db_perform('app_ext_processes_actions', $sql_data);

            $insert_id = db_insert_id();
        }

        redirect_to('ext/processes/actions', 'process_id=' . _get::int('process_id'));
        break;

    case 'delete':
        if(isset($_GET['id']))
        {
            $obj = db_find('app_ext_processes_actions', $_GET['id']);

            db_query("delete from app_ext_processes_actions where id='" . db_input($_GET['id']) . "'");
            db_query("delete from app_ext_processes_actions_fields where actions_id='" . db_input($_GET['id']) . "'");

            db_query("delete from app_ext_processes_clone_subitems where actions_id='" . db_input($_GET['id']) . "'");

            $reports_info_query = db_query("select * from app_reports where reports_type='process_action" . $_GET['id'] . "'");
            if($reports_info = db_fetch_array($reports_info_query))
            {
                db_query("delete from app_reports_filters where reports_id='" . db_input($reports_info['id']) . "'");
                db_query("delete from app_reports where id='" . db_input($reports_info['id']) . "'");
            }

            redirect_to('ext/processes/actions', 'process_id=' . _get::int('process_id'));
        }
        break;
    case 'actions_type_settings':

        $entities_id = _get::int('entities_id');
        $entity_cfg = entities::get_cfg($entities_id);
        $type = $_POST['type'];
        $html = '';

        if(isset($_POST['id']))
        {
            $obj = db_find('app_ext_processes_actions', _post::int('id'));
        }
        else
        {
            $obj = db_show_columns('app_ext_processes_actions');
        }

        $settigns = new settings($obj['settings']);


        switch(true)
        {
            case strstr($type, 'insert_item_entity_'):
                $html .= '
                    <div class="form-group">
                        <label class="col-md-3 control-label" ></label>
                        <div class="col-md-9">' . alert_warning(TEXT_EXT_PROCESS_ACTION_INSERT_ITEM_INFO) . '</div>
                    </div>';
                break;
            case strstr($type, 'runphp_item_entity_'):
                $html .= '            
                            <div class="form-group">
                                <label class="col-md-3 control-label" >' . TEXT_PHP_CODE  . fields::get_available_fields_helper($entities_id, 'settings_php_code'). '</label>
                                <div class="col-md-9">
                                      ' . textarea_tag('settings[php_code]',  $settigns->get('php_code'), array('class' => 'form-control input-small is_codemirror')) . '
                                      ' . tooltip_text(TEXT_EXT_PROCESS_ACTION_RUN_PHP_TIP). '    
                                </div>
                            </div>
                            ' . app_include_codemirror(['javascript','php','clike','css','xml']) . '
                            <script>
                            var myCodeMirrorsettings_php_code = false
                            var php_code  = function () { 
                            myCodeMirrorsettings_php_code = CodeMirror.fromTextArea(document.getElementById("settings_php_code"), {
                            mode: {name: "php",startOpen: true},
                            lineNumbers: true,       
                            autofocus:true,
                            matchBrackets: true,
                            lineWrapping: true,
                            theme: app_skin_dir==\'Dark_Mode\' ? \'darcula\':\'default\',
                            extraKeys: {
                    		     "F11": function(cm) {
                    		       cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                    		     },
                    		     "Esc": function(cm) {
                    		      if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                    		    },                    		    
                    		  }   
                            })  
                            }
                            
                            setTimeout(php_code, 100);
                            </script>
                            
                            <div class="form-group">
                                <label class="col-md-3 control-label" >' . TEXT_DEBUG_MODE . '</label>
                                <div class="col-md-9">
                                      ' . select_tag('settings[debug_mode]', ['0'=>TEXT_NO,1=>TEXT_YES], $settigns->get('debug_mode'), array('class' => 'form-control input-small')) . '
                                </div>
                            </div>    
                            ';
                break;
            case strstr($type, 'edit_item_subentity_'):
                $html .= '            
                            <div class="form-group">
                                <label class="col-md-3 control-label" >' . TEXT_EXT_APPLY_ENTITY_ACCESS_RULES . '</label>
                                <div class="col-md-9">
                                      ' . select_tag('settings[apply_entity_access_rules]', ['0'=>TEXT_NO,1=>TEXT_YES], $settigns->get('apply_entity_access_rules'), array('class' => 'form-control input-small')) . '
                                </div>
                            </div>
                            ';
                break;
            case strstr($type, 'edit_item_related_entity_'):
                $html .= '            
                            <div class="form-group">
                                <label class="col-md-3 control-label" >' . TEXT_EXT_APPLY_ENTITY_ACCESS_RULES . '</label>
                                <div class="col-md-9">
                                      ' . select_tag('settings[apply_entity_access_rules]', ['0'=>TEXT_NO,1=>TEXT_YES], $settigns->get('apply_entity_access_rules'), array('class' => 'form-control input-small')) . '
                                </div>
                            </div>
                            ';
                break;
            case strstr($type, 'repeat_item_entity_'):

                $html = '
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="repeat_type">' . TEXT_EXT_EVENT_REPEAT_TYPE . '</label>
                      <div class="col-md-9">	
                          ' . select_tag('settings[repeat_type]', recurring_tasks::get_repeat_types(), $settigns->get('repeat_type'), array('class' => 'form-control input-medium required', 'onChange' => 'display_repeat_days_by_type()')) . '        
                      </div>			
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="repeat_interval">' . TEXT_EXT_EVENT_REPEAT_INTERVAL . '</label>
                      <div class="col-md-9">	
                          ' . input_tag('settings[repeat_interval]', (strlen($settigns->get('repeat_interval')) ? $settigns->get('repeat_interval') : 1), array('class' => 'form-control input-xsmall')) . '        
                      </div>			
                    </div>

                    <div class="form-group" id="repeat-days-form-group" style="display:none">
                        <label class="col-md-3 control-label" for="repeat_days">' . TEXT_EXT_EVENT_REPEAT_DAYS . '</label>
                      <div class="col-md-9">	
                          ' . select_checkboxes_tag('settings[repeat_days]', calendar::get_events_repeat_days(), $settigns->get('repeat_days'), array('class' => 'form-control required')) . '        
                      </div>			
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="repeat_time">' . TEXT_EXT_REPEAT_TIME . '</label>
                      <div class="col-md-9">	
                          ' . select_tag('settings[repeat_time]', recurring_tasks::get_repeat_time_choices(), $settigns->get('repeat_time'), array('class' => 'form-control input-small')) . '        
                      </div>			
                    </div>


                    <div class="form-group">
                        <label class="col-md-3 control-label" for="repeat_start">' . TEXT_EXT_REPEAT_START . '</label>
                      <div class="col-md-9">    
                        <div class="input-group input-medium date datepicker"> 
                          ' . input_tag('settings[repeat_start]', (strlen($settigns->get('repeat_start')) ? $settigns->get('repeat_start') : date('Y-m-d')), array('class' => 'form-control required')) . ' 
                          <span class="input-group-btn">
                            <button class="btn btn-default date-set" type="button"><i class="fa fa-calendar"></i></button>
                          </span>
                        </div>        
                      </div>			
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="repeat_end">' . TEXT_EXT_REPEAT_END . '</label>
                      <div class="col-md-9">    
                        <div class="input-group input-medium date datepicker"> 
                          ' . input_tag('settings[repeat_end]', $settigns->get('repeat_end'), array('class' => 'form-control')) . ' 
                          <span class="input-group-btn">
                            <button class="btn btn-default date-set" type="button"><i class="fa fa-calendar"></i></button>
                          </span>
                        </div>        
                      </div>			
                    </div> 

                    <div class="form-group">
                        <label class="col-md-3 control-label" for="repeat_limit">' . TEXT_EXT_EVENT_REPEAT_LIMIT . '</label>
                      <div class="col-md-9">	
                          ' . input_tag('settings[repeat_limit]', $settigns->get('repeat_limit'), array('class' => 'form-control input-xsmall')) . '
                          ' . tooltip_text(TEXT_EXT_NUMBER_REPETITIONS_INFO) . '        
                      </div>			
                    </div>
                    
                    <script>
                        display_repeat_days_by_type();

                        function display_repeat_days_by_type()
                        {
                          if($("#settings_repeat_type").val()=="weekly")
                          {
                            $("#repeat-days-form-group").fadeIn();
                          }
                          else
                          {
                            $("#repeat-days-form-group").fadeOut();
                          }
                        }
                    </script>
                    ';
                break;
            case strstr($type, 'save_export_template_entity_'):

                $save_export_template = $settigns->get('save_export_template');

                $choices = ['' => ''];
                $templates_query = db_query("select id, name, type from app_ext_export_templates where entities_id='" . $entities_id . "'");
                while($templates = db_fetch_array($templates_query))
                {
                    if($templates['type'] == 'docx')
                    {
                        $choices[$templates['id'] . '_pdf'] = $templates['name'] . ' (PDF)';
                        $choices[$templates['id'] . '_docx'] = $templates['name'] . ' (DOCX)';
                    }
                    elseif($templates['type'] == 'xlsx')
                    {
                        $choices[$templates['id'] . '_xlsx'] = $templates['name'] . ' (XLSX)';
                    }
                    else
                    {
                        $choices[$templates['id'] . '_pdf'] = $templates['name'] . ' (PDF)';
                    }
                }

                $html .= '
                                    <div class="form-group">
                                        <label class="col-md-3 control-label" ></label>
                                        <div class="col-md-9">
                                              ' . tooltip_text(TEXT_EXT_SELECT_TEMPLATES_TO_SAVE) . '
                                        </div>
                                    </div>                                
                                ';

                $fields_query = db_query("select id, name, type from app_fields where entities_id='" . $entities_id . "' and type in ('fieldtype_input_file','fieldtype_attachments','fieldtype_onlyoffice')");
                while($fields = db_fetch_array($fields_query))
                {
                    if($fields['type'] == 'fieldtype_input_file')
                    {
                        $html .= '
                                    <div class="form-group">
                                        <label class="col-md-3 control-label" >' . $fields['name'] . '</label>
                                        <div class="col-md-9">
                                              ' . select_tag('settings[save_export_template][' . $fields['id'] . ']', $choices, ($save_export_template[$fields['id']] ?? ''), array('class' => 'form-control chosen-select')) . '
                                        </div>
                                    </div>                                
                                ';
                    }
                    elseif(in_array($fields['type'], ['fieldtype_attachments', 'fieldtype_onlyoffice']))
                    {
                        $html .= '
                                    <div class="form-group">
                                        <label class="col-md-3 control-label" >' . $fields['name'] . '</label>
                                        <div class="col-md-9">
                                              ' . select_tag('settings[save_export_template][' . $fields['id'] . '][]', $choices, ($save_export_template[$fields['id']] ?? ''), array('class' => 'form-control chosen-select', 'multiple' => true)) . '
                                        </div>
                                    </div>                                
                                ';
                    }
                }
                break;
            case strstr($type, 'unlink_records_by_mysql_query_'):
                $html .= '
                        <div class="form-group">
                                <label class="col-md-3 control-label" for="settings_copy_comments">' . TEXT_FIELDTYPE_MYSQL_QUERY_WHERE_QUERY . '</label>
                            <div class="col-md-9">
                                  ' . textarea_tag('settings[where_query]', $settigns->get('where_query'), array('class' => 'form-control required code')) . '
                                  ' . tooltip_text(TEXT_EXT_PROCESS_ACTION_UNLINK_RECORDS_BY_MYSQL_QUERY_INFO) . '
                            </div>
                        </div>';
                break;    
            case strstr($type, 'link_records_by_mysql_query_'):
                $html .= '
                        <div class="form-group">
                                <label class="col-md-3 control-label" for="settings_copy_comments">' . TEXT_FIELDTYPE_MYSQL_QUERY_WHERE_QUERY . '</label>
                            <div class="col-md-9">
                                  ' . textarea_tag('settings[where_query]', $settigns->get('where_query'), array('class' => 'form-control required code')) . '
                                  ' . tooltip_text(TEXT_EXT_PROCESS_ACTION_LINK_RECORDS_BY_MYSQL_QUERY_INFO) . '
                            </div>
                        </div>';
                break;
            case strstr($type, 'clone_item_entity_'):

                $choices = array();

                foreach(entities::get_tree(0, [], 0, [], [1]) as $v)
                {
                    $choices[$v['id']] = str_repeat('- ', $v['level']) . $v['name'];
                }

                $html .= '
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="settings_copy_sub_entities">' . TEXT_EXT_CLONE_TO_ENTITY . '</label>
                            <div class="col-md-9">
                                  ' . select_tag('settings[clone_to_entity][]', $choices, $settigns->get('clone_to_entity'), array('class' => 'form-control chosen-select')) . '
                            </div>
                        </div>
                        <div id="parent_dropdown_settings"></div>
                        <script>
                            function get_parent_dropdown_settings()
                            {
                                $("#parent_dropdown_settings").load("' . url_for("ext/processes/actions", "process_id=" . $app_process_info['id'] . '&action=get_parent_dropdown_settings') . '",{entity_id: $("#settings_clone_to_entity").val(), id:"' . $obj['id'] . '"},function(){
                                        appHandleUniform();
                                    })
                            }
                            
                            $("#settings_clone_to_entity").change(function(){
                                get_parent_dropdown_settings()
                            })
                            
                            get_parent_dropdown_settings()
                        </script>';
                
                if(listing_types::has_tree_table($entities_id))
                {
                    $html .= '            
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="settings_copy_sub_entities">' . TEXT_EXT_CLONE_NESTED_ITEMS . '</label>
                                <div class="col-md-9">
                                      ' . select_tag('settings[clone_nested_items]', ['0'=>TEXT_NO,1=>TEXT_YES], $settigns->get('clone_nested_items'), array('class' => 'form-control input-small')) . '
                                </div>
                            </div>
                            ';
                }
                
                break;
            case strstr($type, 'copy_item_entity_'):

                //copy comment
                if($entity_cfg['use_comments'] == 1)
                {
                    $html .= '
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="settings_copy_comments">' . TEXT_EXT_COPY_COMMENTS . '</label>
                                <div class="col-md-9">
                                      ' . select_tag('settings[copy_comments]', ['0' => TEXT_NO, '1' => TEXT_YES], $settigns->get('copy_comments'), array('class' => 'form-control input-small')) . '
                                </div>
                             </div>
                                    ';
                }

                //copy related items
                $choices = [];
                $fields_query = db_query("select f.id, f.name from app_fields f, app_forms_tabs t where f.type in ('fieldtype_related_records') and f.entities_id='" . db_input($entities_id) . "' and f.forms_tabs_id=t.id");
                while($field = db_fetch_array($fields_query))
                {
                    $choices[$field['id']] = $field['name'];
                }

                if(count($choices))
                {
                    $html .= '
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="settings_copy_related_items">' . TEXT_EXT_COPY_RELATE_RECORDS . '</label>
                                <div class="col-md-9">	
                                      ' . select_tag('settings[copy_related_items][]', $choices, $settigns->get('copy_related_items'), array('class' => 'form-control chosen-select', 'multiple' => 'multiple')) . '
                                </div>			
                             </div>
                                    ';
                }

                //coy sub entities
                $choices = [];
                $entities_query = db_query("select * from app_entities where parent_id='" . db_input($entities_id) . "' order by sort_order,name");
                while($entities = db_fetch_array($entities_query))
                {
                    $choices[$entities['id']] = $entities['name'];
                }

                if(count($choices))
                {
                    $html .= '
                            <div class="form-group">
                                    <label class="col-md-3 control-label" for="settings_copy_sub_entities">' . TEXT_EXT_COPY_SUB_ENTITIES . '</label>
                                <div class="col-md-9">
                                      ' . select_tag('settings[copy_sub_entities][]', $choices, $settigns->get('copy_sub_entities'), array('class' => 'form-control chosen-select', 'multiple' => 'multiple')) . '
                                </div>
                             </div>
							';
                }
                
                if(listing_types::has_tree_table($entities_id))
                {
                    $html .= '<div class="form-group">
                                <label class="col-md-3 control-label" for="settings_copy_sub_entities">' . TEXT_EXT_COPY_NESTED_ITEMS . '</label>
                                <div class="col-md-9">
                                      ' . select_tag('settings[copy_nested_items]', ['0'=>TEXT_NO,1=>TEXT_YES], $settigns->get('copy_nested_items'), array('class' => 'form-control input-small')) . '
                                </div>
                            </div>';
                }
                
                
                $html .= '
                    <div class="form-group">
                        <label class="col-md-3 control-label">' . TEXT_EXT_NUMBER_OF_COPIES . '</label>
                        <div class="col-md-9">' . input_tag('settings[number_of_copies]',$settigns->get('number_of_copies',1),['class'=>'form-control input-small','type'=>'number','min'=>1]) .'</div>
                    </div>
                    ';

                break;
        }

        echo $html;

        exit();
        break;
        
    case 'get_parent_dropdown_settings':
        
        if(isset($_POST['id']))
        {
            $obj = db_find('app_ext_processes_actions', _post::int('id'));
        }
        else
        {
            $obj = db_show_columns('app_ext_processes_actions');
        }

        $settigns = new settings($obj['settings']);
        
        $entity_id = _POST('entity_id');
        
        $parent_entity_id = $app_entities_cache[$entity_id]['parent_id'];        
        if($parent_entity_id>0)
        {
            $choices = [];
            
            $fields_query = fields::get_query($parent_entity_id, " and type in (" . fields_types::get_types_for_search_list() . ")");
            while($fields = db_fetch_array($fields_query))
            {
                $choices[$fields['id']] = fields::get_name($fields); 
            }

            //$choices

            $html = '
                <div class="form-group">
                        <label class="col-md-3 control-label" for="settings_copy_sub_entities">' . TEXT_NAV_LISTING_CONFIG  . ' (' . TEXT_SEARCH_BY_FIELDS . ')</label>
                    <div class="col-md-9">
                          ' . select_tag('settings[parent_entity_search_fields][]', $choices, $settigns->get('parent_entity_search_fields'), array('class' => 'form-control chosen-select', 'multiple' => 'multiple')) . '
                          ' . tooltip_text( TEXT_ENTITY . ': ' . $app_entities_cache[$parent_entity_id]['name'] ) . '
                    </div>
                 </div>
                ';

            echo $html;
        }
        
        exit();
        break;    
}