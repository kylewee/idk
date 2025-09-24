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


namespace report_page;

class report_filters
{
    private $report,
            $settings,
            $form_css,
            $from_function;
    
    const   COUNT_EXTRA_FILTERS = 3;
    
    function __construct($report)
    {
        $this->report = $report;
        
        $this->settings = new \settings($this->report['settings']); 
        
        $this->form_css = 'from-report-page-filters';
        $this->from_function = 'load_report_page()';        
    }
    
    function set_form_css($v)
    {
        $this->form_css = $v;
    }
    
    function set_form_function($v)
    {
        $this->from_function = $v;
    }
    
    function render()
    {                
        $html = $this->render_fitler_by_date() . 
                $this->render_fitler_by_user() .
                $this->render_fitler_by_entity() . 
                $this->render_fitler_by_list();
        
        if(strlen($html))
        {
            $html = '            
                <form class="form-inline ' . $this->form_css . ' noprint" role="form">
                    ' . $html . '
                </form>
                ';
        }
        
        return $html;
    }
    
    function render_fitler_by_user()
    {
        global $app_users_cache, $report_page_filters;
        
        if($this->settings->get('filter_by_user')!=1) return '';
        
        $heading = strlen($this->settings->get('filter_by_user_heading')) ? $this->settings->get('filter_by_user_heading') : TEXT_USER;                        
        
        $allowed_groups = $this->settings->get('filter_by_user_allowed_groups');
                
        $choices = [];    
        $order_by_sql = (CFG_APP_DISPLAY_USER_NAME_ORDER == 'firstname_lastname' ? ' u.field_7, u.field_8' : ' u.field_8, u.field_7');
        $where_sql = is_array($allowed_groups) ? " and u.field_6 in (" . implode(',',$allowed_groups). ")":'';
        $users_query = db_query("select u.*,a.name as group_name from app_entity_1 u left join app_access_groups a on a.id=u.field_6 where u.field_5=1 {$where_sql} order by " . $order_by_sql,false);
        while ($users = db_fetch_array($users_query))
        {                    
            $choices[$users['id']] = $app_users_cache[$users['id']]['name']; 
        }
        
        $html = '
            <div class="form-group">
                <label>' . $heading . ': </label>
                <label>' . select_tag('filter_by_user',$choices,$report_page_filters[$this->report['id']]['filter_by_user']??'',['class'=>'form-control input-large chosen-select report_page_' . $this->report['id'] . '_filter_by_user']). '</label>
            </div>    
            <script>
            $(function(){
              $(".report_page_' . $this->report['id'] . '_filter_by_user").change(function(){
                ' . $this->from_function  . '
              })  
            })
            </script>
            ';
                
        return $html;
    }
    
    function render_fitler_by_date()
    {
        global $report_page_filters;
        
        if($this->settings->get('filter_by_date')!=1) return '';
        
        //handle date range
        if($this->settings->get('filter_by_date_mode')=='date_range')
        {
            return $this->render_fitler_by_date_range();
        }
        
        //settings
        $format = 'yyyy-mm-dd';
        $minViewMode = 'days';
        
        switch($this->settings->get('filter_by_date_mode'))
        {
            case 'day':
                $format = 'yyyy-mm-dd';
                $minViewMode = 'days';
                break;
            case 'month':
                $format = 'yyyy-mm';
                $minViewMode = 'months';
                break;
            case 'year':
                $format = 'yyyy';
                $minViewMode = 'years';
                break;
        }
        
        $heading = strlen($this->settings->get('filter_by_date_heading')) ? $this->settings->get('filter_by_date_heading') : TEXT_DATE;
        
        $html = '
            <div class="form-group">
                <label>' . $heading . ': </label>
                <label><div class="input-group input-medium date report-page-datepicker report-page' . $this->report['id'] . '-datepicker">' . input_tag('filter_by_date', $this->set_fitler_by_date_value(), ['class'=>'form-control','readonly'=>'readonly']) . '<span class="input-group-btn"><button class="btn btn-default date-set" type="button"><i class="fa fa-calendar"></i></button></span></div></label>    
            </div>
            
            <script>
            $(function(){
                $(function(){
                    $(".report-page' . $this->report['id'] . '-datepicker").datepicker({
                        rtl: App.isRTL(),
                        autoclose: true,
                        weekStart: app_cfg_first_day_of_week,
                        format: "' . $format . '",
                        clearBtn: false,
                        minViewMode: "' . $minViewMode . '",                      

                    }).on("changeDate",function(e){
                        ' . $this->from_function  . '
                    });
                })
            })
            </script>
            ';
        
        return $html;
    }
    
    function render_fitler_by_date_range()
    {
        $html = '
            <div class="form-group">
	
		<div class="input-group input-large datepicker input-daterange daterange-filter-' . $this->report['id'] . '">					
			<span class="input-group-addon">
				<i class="fa fa-calendar"></i>
			</span>
			' . input_tag('filter_by_date_from',$this->get_filter_by_date_range('from'),array('class'=>'form-control','placeholder'=>TEXT_DATE_FROM)) . '
			<span class="input-group-addon">
				<i style="cursor:pointer" class="fa fa-refresh" aria-hidden="true" title="'. TEXT_EXT_RESET . '" onClick="reset_date_range_filter' . $this->report['id'] . '()"></i>
			</span>
			' . input_tag('filter_by_date_to',$this->get_filter_by_date_range('to'),array('class'=>'form-control','placeholder'=>TEXT_DATE_TO)) . '
                        <span class="btn input-group-addon" title="'. TEXT_SEARCH . '" onClick="apply_date_range_filter' . $this->report['id'] . '()">
				<i style="cursor:pointer" class="fa fa-search" aria-hidden="true" title="'. TEXT_SEARCH . '" ></i>
			</span>    
		</div>		
	</div>
        
        <script>
            function reset_date_range_filter' . $this->report['id'] . '()
            {	
                $(".daterange-filter-' . $this->report['id'] . ' [name=filter_by_date_from]").val("' . date('Y-m-d'). '")
                $(".daterange-filter-' . $this->report['id'] . ' [name=filter_by_date_to]").val("' . date('Y-m-d'). '")  
                
                ' . $this->from_function  . '    
            }
            
            function apply_date_range_filter' . $this->report['id'] . '()
            {
                ' . $this->from_function  . '
            }
        </script>
        ';
        
        return $html;
    }
    
    function get_filter_by_date_range($type)
    {
        global $report_page_filters;
        
        return $report_page_filters[$this->report['id']]['filter_by_date_' . $type ]??date('Y-m-d');
    }
    
    function set_fitler_by_date_value()
    {
        global $report_page_filters;
        
        if(isset($report_page_filters[$this->report['id']]['filter_by_date']))
        {
            return $report_page_filters[$this->report['id']]['filter_by_date'];
        }
                        
        switch($this->settings->get('filter_by_date_mode'))
        {
            case 'day':
                return date('Y-m-d');
                break;
            case 'month':
                return date('Y-m');
                break;
            case 'year':
                return  date('Y');
                break;
            default:
                return  date('Y-m-d');
                break;
        }
    }
    
    static function set_filters($report)
    {
        global $report_page_filters;
        
        //print_rr($_POST);
        
        $filter_by_date = $_POST['filter_by_date'] ?? date('Y-m-d');
        
        $report_page_filters[$report['id']]['filter_by_date'] = substr($filter_by_date,0,10);                
        $report_page_filters[$report['id']]['filter_by_user'] = isset($_POST['filter_by_user']) ? (int)$_POST['filter_by_user'] : 0;   
        
        for($i=1;$i<=self::COUNT_EXTRA_FILTERS;$i++)
        {
            $report_page_filters[$report['id']]['filter_by_entity' . $i] = isset($_POST['filter_by_entity'][$report['id']][$i]) ? (int)$_POST['filter_by_entity'][$report['id']][$i] : 0;   
        }
        
        for($i=1;$i<=self::COUNT_EXTRA_FILTERS;$i++)
        {
            $report_page_filters[$report['id']]['filter_by_list' . $i] = isset($_POST['filter_by_list' . $i]) ? (int)$_POST['filter_by_list' . $i] : 0;   
        }
        
        $filter_by_date_from = $_POST['filter_by_date_from'] ?? date('Y-m-d');        
        $report_page_filters[$report['id']]['filter_by_date_from'] = substr($filter_by_date_from,0,10); 
        
        $filter_by_date_to = $_POST['filter_by_date_to'] ?? date('Y-m-d');        
        $report_page_filters[$report['id']]['filter_by_date_to'] = substr($filter_by_date_to,0,10);
    }
    
    function render_entities_filters_form()
    {                
        $html = '';
        for($i=1;$i<=self::COUNT_EXTRA_FILTERS;$i++)
        {
            $html .= '
                <div class="form-group">
                    <label class="col-md-3 control-label">' . TEXT_FILTER_BY_ENTITY . ' ' . $i . '</label>
                    <div class="col-md-9">	
                        ' . select_tag_toogle('settings[filter_by_entity' . $i . ']', $this->settings->get('filter_by_entity' . $i)) .'      
                    </div>			
                </div>
                <div class="form-group" form_display_rules="settings_filter_by_entity' . $i . ':1">
                    <label class="col-md-3 control-label" >'  . TEXT_ENTITY . ' ' . $i . '</label>
                    <div class="col-md-9">	
                         ' . select_tag('settings[filter_by_entity' . $i  . '_id]', \entities::get_choices(), $this->settings->get('filter_by_entity' . $i  . '_id'), ['class' => 'form-control input-xlarge chosen-select']) . '      
                    </div>			
                </div>
                <div class="form-group" form_display_rules="settings_filter_by_entity' . $i . ':1">
                    <label class="col-md-3 control-label">' . TEXT_HEADING . ' ' . $i . '</label>
                    <div class="col-md-9">	
                        ' . input_tag('settings[filter_by_entity' . $i  . '_heading]', $this->settings->get('filter_by_entity' . $i  . '_heading'),['class'=>'form-control input-large']) . '                              
                    </div>			
                </div> 
                ';
        }
        
        return $html;
    }
    
    function render_fitler_by_entity()
    {
        global $app_entities_cache, $report_page_filters; 
                                
        $html = '';
        for($i=1;$i<=self::COUNT_EXTRA_FILTERS;$i++)
        {
            if($this->settings->get('filter_by_entity' . $i)!=1) continue;
            
            $entity_id = $this->settings->get('filter_by_entity' . $i  . '_id');
                                    
            $heading = strlen($this->settings->get('filter_by_entity' . $i  . '_heading')) ? $this->settings->get('filter_by_entity' . $i  . '_heading') : $app_entities_cache[$entity_id]['name'];
            
            $value = $report_page_filters[$this->report['id']]['filter_by_entity' . $i]??0;
            
            $choices = [];
            
            if($value)
            {
                $choices[$value] = \items::get_heading_field($entity_id, $value);
            }
            
            $html .= '
            <div class="form-group">
                <label>' . $heading . ': </label>
                
                <label style="width: 320px;">' . select_entities_tag('filter_by_entity[' . $this->report['id'] . '][' . $i. ']', $choices, $value,['entities_id'=>$entity_id,'class'=>'report_page_' . $this->report['id'] . '_filter_by_entity' .$i]). '</label>    
            </div>    
            <script>
            $(function(){
              $(".report_page_' . $this->report['id'] . '_filter_by_entity' . $i . '").change(function(){
                ' . $this->from_function  . '
              })  
            })
            </script>
            ';
        }
        
        return $html;
    }
    
    function render_list_filters_form()
    {                
        $html = '';
        for($i=1;$i<=self::COUNT_EXTRA_FILTERS;$i++)
        {
            $html .= '
                <div class="form-group">
                    <label class="col-md-3 control-label">' . TEXT_FILTER_BY_GLOBAL_LIST . ' ' . $i . '</label>
                    <div class="col-md-9">	
                        ' . select_tag_toogle('settings[filter_by_list' . $i . ']', $this->settings->get('filter_by_list' . $i)) .'      
                    </div>			
                </div>
                <div class="form-group" form_display_rules="settings_filter_by_list' . $i . ':1">
                    <label class="col-md-3 control-label" >'  . TEXT_HEADING_GLOBAL_LIST_IFNO . ' ' . $i . '</label>
                    <div class="col-md-9">	
                         ' . select_tag('settings[filter_by_list' . $i  . '_id]', \global_lists::get_lists_choices(), $this->settings->get('filter_by_list' . $i  . '_id'), ['class' => 'form-control input-xlarge chosen-select']) . '      
                    </div>			
                </div>
                <div class="form-group" form_display_rules="settings_filter_by_list' . $i . ':1">
                    <label class="col-md-3 control-label">' . TEXT_HEADING . ' ' . $i . '</label>
                    <div class="col-md-9">	
                        ' . input_tag('settings[filter_by_list' . $i  . '_heading]', $this->settings->get('filter_by_list' . $i  . '_heading'),['class'=>'form-control input-large']) . '                              
                    </div>			
                </div> 
                ';
        }
        
        return $html;
    }
    
    function render_fitler_by_list()
    {
        global $app_entities_cache, $report_page_filters, $app_global_choices_cache; 
                                
        $html = '';
        for($i=1;$i<=self::COUNT_EXTRA_FILTERS;$i++)
        {
            if($this->settings->get('filter_by_list' . $i)!=1) continue;
            
            $list_id = $this->settings->get('filter_by_list' . $i  . '_id');
                                                            
            $heading = strlen($this->settings->get('filter_by_list' . $i  . '_heading')) ? $this->settings->get('filter_by_list' . $i  . '_heading') : \global_lists::get_name_by_id($list_id);
            
            $value = $report_page_filters[$this->report['id']]['filter_by_list' . $i]??0;                        
            
            $html .= '
            <div class="form-group">
                <label>' . $heading . ': </label>
                
                <label >' . select_tag('filter_by_list' . $i, \global_lists::get_choices($list_id), $value,['class'=>'form-control input-medium chosen-select report_page_' . $this->report['id'] . '_filter_by_list' .$i]). '</label>    
            </div>    
            <script>
            $(function(){
              $(".report_page_' . $this->report['id'] . '_filter_by_list' . $i . '").change(function(){
                ' . $this->from_function  . '
              })  
            })
            </script>
            ';
        }
        
        return $html;
    }
    
    
}
