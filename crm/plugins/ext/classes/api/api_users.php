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

class api_users extends api
{
    private $menu;
    
    function __construct($user)
    {
        $this->user = $user;        
    }
    
    function get_users_menu()
    {
        
        $custom_entities_menu = array();
        $custom_reports_menu = array();
        $menu_query = db_fetch_all('app_entities_menu', 'length(entities_list)>0', 'sort_order, name');
        while($v = db_fetch_array($menu_query))
        {
            $custom_entities_menu = array_merge($custom_entities_menu, explode(',', $v['entities_list']));
            
            if(strlen($v['reports_list']))
            {
                $custom_reports_menu = array_merge($custom_reports_menu, explode(',', str_replace(entities_menu::get_reports_types(),'',$v['reports_list'])));
            }
        }

        $where_sql = $where_reports_sql = '';

        if(count($custom_entities_menu) > 0)
        {
            $where_sql = " and e.id not in (" . implode(',', $custom_entities_menu) . ")";
        }
        
        if(count($custom_reports_menu) > 0)
        {
            $where_reports_sql = " and r.id not in (" . implode(',', $custom_reports_menu) . ")";            
        }
    
        $menu = [];
        
        //entities
        if($this->user['group_id'] == 0)
        {
            $entities_query = db_query("select * from app_entities e where (e.parent_id = 0 or e.display_in_menu=1) {$where_sql} order by e.sort_order, e.name");
        }
        else
        {
            $entities_query = db_query("select e.* from app_entities e, app_entities_access ea where e.id=ea.entities_id and length(ea.access_schema)>0 and ea.access_groups_id='" . db_input($this->user['group_id']) . "' and (e.parent_id = 0 or display_in_menu=1) {$where_sql} order by e.sort_order, e.name");
        }
        
        while($entities = db_fetch_array($entities_query))
        {
            if($entities['parent_id'] == 0)
            {
                $reports_info = reports::create_default_entity_report($entities['id'], 'entity');
            }
            else
            {
                $reports_info = reports::create_default_entity_report($entities['id'], 'entity_menu');                
            }
            
            $entity_cfg = new entities_cfg($entities['id']);
            $menu_title = (strlen($entity_cfg->get('menu_title')) > 0 ? $entity_cfg->get('menu_title') : $entities['name']);
            $menu_icon = (strlen($entity_cfg->get('menu_icon')) > 0 ? $entity_cfg->get('menu_icon') : 'fa-reorder');

            $menu[] = [
                'title' => $menu_title, 
                'reports_id' => $reports_info['id'], 
                'entities_id' => $reports_info['entities_id'],
                'nested_entities' => entities::get_nested_choices($entities['id']),
                'class' => $menu_icon,
                'icon_color' => $entity_cfg->get('menu_icon_color'),
                'bg_color' => $entity_cfg->get('menu_bg_color'),
                'type' =>$reports_info['reports_type'],
            ];            
        }
        
        //custom menu
        $menu = $this->build_custom_entities_menu($menu);
        
        //get standard reports
        $reports_query = db_query("select r.* from app_reports r where r.created_by='" . db_input($this->user['id']) . "' and r.reports_type in ('standard') {$where_reports_sql} order by name");
        while($v = db_fetch_array($reports_query))
        {
            $menu[] = [
                'title' => $v['name'],
                'reports_id' => $v['id'],
                'entities_id' => $v['entities_id'], 
                'class' => (strlen($v['menu_icon']) > 0 ? $v['menu_icon'] : 'fa-list-alt'),
                'icon_color' => $v['icon_color'],
                'bg_color' => $v['bg_color'],
                'type' =>$v['reports_type'],
            ];
        }
        
         //get common reports      
        if($this->user['group_id']>0)
        {    
            $reports_query = db_query("select r.* from app_reports r, app_entities e, app_entities_access ea  where r.entities_id = e.id and e.id=ea.entities_id and length(ea.access_schema)>0 and ea.access_groups_id='" . db_input($this->user['group_id']) . "' and (find_in_set(" . $this->user['group_id'] . ",r.users_groups) or find_in_set(" . $this->user['id'] . ",r.assigned_to) ) and r.reports_type = 'common' {$where_reports_sql} order by r.dashboard_sort_order, name");
        }
        else
        {
            $reports_query = db_query("select r.* from app_reports r, app_entities e where r.entities_id = e.id and (find_in_set(" . $this->user['group_id'] . ",r.users_groups) or find_in_set(" . $this->user['id'] . ",r.assigned_to) )  and r.reports_type = 'common' {$where_reports_sql} order by r.dashboard_sort_order, r.name"); 
        }
        
        while($v = db_fetch_array($reports_query))
        {
            $menu[] = [
                'title' => $v['name'],
                'reports_id' => $v['id'],
                'entities_id' => $v['entities_id'], 
                'class' => (strlen($v['menu_icon']) > 0 ? $v['menu_icon'] : 'fa-list-alt'),
                'icon_color' => $v['icon_color'],
                'bg_color' => $v['bg_color'],
                'type' =>$v['reports_type'],
            ];
        }
        
        self::response_success($menu);
    }
    
    function build_custom_entities_menu($menu, $parent_id = 0, $level = 0)
    {        
        if($level > 3)
            return [];

        $custom_entities_menu = array();
        $entities_menu_query = db_fetch_all('app_entities_menu', 'parent_id=' . $parent_id, 'sort_order, name');
        while($entities_menu = db_fetch_array($entities_menu_query))
        {
            $sub_menu = array();

            //add entities
            if(strlen($entities_menu['entities_list']) and $entities_menu['type']=='entity')
            {
                $where_sql = " e.id in (" . $entities_menu['entities_list'] . ")";

                if($this->user['group_id'] == 0)
                {
                    $entities_query = db_query("select * from app_entities e where e.id in (" . $entities_menu['entities_list'] . ") order by field(e.id," . $entities_menu['entities_list'] . ")");
                }
                else
                {
                    $entities_query = db_query("select e.* from app_entities e, app_entities_access ea where e.id=ea.entities_id and length(ea.access_schema)>0 and ea.access_groups_id='" . db_input($this->user['group_id']) . "' and e.id in (" . $entities_menu['entities_list'] . ") order by field(e.id," . $entities_menu['entities_list'] . ")");
                }

                while($entities = db_fetch_array($entities_query))
                {
                    if($entities['parent_id'] == 0)
                    {
                        $reports_info = reports::create_default_entity_report($entities['id'], 'entity');
                        
                        $s = array();

                        $entity_cfg = new entities_cfg($entities['id']);
                        $menu_title = (strlen($entity_cfg->get('menu_title')) > 0 ? $entity_cfg->get('menu_title') : $entities['name']);
                        $menu_icon = (strlen($entity_cfg->get('menu_icon')) > 0 ? $entity_cfg->get('menu_icon') : ($entities['id'] == 1 ? 'fa-user' : 'fa-reorder'));

                        $sub_menu[] = array(
                            'title' => $menu_title, 
                            'reports_id' => $reports_info['id'], 
                            'entities_id' => $reports_info['entities_id'],
                            'class' => $menu_icon,
                            'icon_color' => $entity_cfg->get('menu_icon_color'),
                            'bg_color' => $entity_cfg->get('menu_bg_color'),
                            );
                    }
                    else
                    {
                        $reports_info = reports::create_default_entity_report($entities['id'], 'entity_menu');

                        //check if parent reports was not set
                        if($reports_info['parent_id'] == 0)
                        {
                            reports::auto_create_parent_reports($reports_info['id']);
                        }

                        $entity_cfg = new entities_cfg($entities['id']);
                        $menu_title = (strlen($entity_cfg->get('menu_title')) > 0 ? $entity_cfg->get('menu_title') : $entities['name']);
                        $menu_icon = (strlen($entity_cfg->get('menu_icon')) > 0 ? $entity_cfg->get('menu_icon') : ($entities['id'] == 1 ? 'fa-user' : 'fa-reorder'));

                        $sub_menu[] = array(
                            'title' => $menu_title, 
                            'reports_id' => $reports_info['id'], 
                            'entities_id' => $reports_info['entities_id'],
                            'class' => $menu_icon,
                            'icon_color' => $entity_cfg->get('menu_icon_color'),
                            'bg_color' => $entity_cfg->get('menu_bg_color'),
                            );
                    }
                }
            }

            //add reports
            if($entities_menu['type']=='entity')
            {
                $sub_menu = $this->build_custom_reports_menu($entities_menu['reports_list'], $sub_menu);                
            }

            //add urls
            if($entities_menu['type']=='url' and strlen($entities_menu['url']))
            {            
                if((strlen($entities_menu['users_groups']) and in_array($this->user['group_id'],explode(',',$entities_menu['users_groups']))) or strlen($entities_menu['assigned_to']) and in_array($this->user['id'],explode(',',$entities_menu['assigned_to'])))
                {
                    $menu_icon = (strlen($entities_menu['icon']) > 0 ? $entities_menu['icon'] : 'fa-reorder');
                    $sub_menu[] = array(
                        'title' => $entities_menu['name'], 
                        'url' => $entities_menu['url'], 
                        'class' => $menu_icon,                        
                        'icon_color' => $entities_menu['icon_color'],
                        'bg_color' => $entities_menu['bg_color'],
                        );                
                }

            }       

            $sub_menu = $this->build_custom_entities_menu($sub_menu, $entities_menu['id'], $level + 1);

            $nested_query = db_query("select id from app_entities_menu where parent_id='" . $entities_menu['id'] . "' limit 1");
            $has_nested = db_fetch_array($nested_query);

            if(count($sub_menu) == 1 and !$has_nested)
            {
                $menu_icon = (strlen($entities_menu['icon']) > 0 ? $entities_menu['icon'] : 'fa-reorder');
                $menu[] = array(
                    'title' => $entities_menu['name'],                      
                    'reports_id' => $sub_menu[0]['reports_id'], 
                    'entities_id' => $sub_menu[0]['entities_id'],
                    'class' => $menu_icon,
                    'icon_color' => $entities_menu['icon_color'],
                    'bg_color' => $entities_menu['bg_color']);
            }
            elseif(count($sub_menu) > 0)
            {
                $menu_icon = (strlen($entities_menu['icon']) > 0 ? $entities_menu['icon'] : 'fa-reorder');
                $menu[] = array(
                    'title' => $entities_menu['name'],                     
                    'class' => $menu_icon, 
                    'icon_color' => $entities_menu['icon_color'],
                    'bg_color' => $entities_menu['bg_color'],
                    'submenu' => $sub_menu);
            }
        }        

        return $menu;
    }    
    
    
    function build_custom_reports_menu($reports_list, $sub_menu)
    {        
        if(!strlen($reports_list))
            return $sub_menu;

        foreach(explode(',', $reports_list) as $reports_type)
        {
            $reports_id = str_replace(entities_menu::get_reports_types(), '', $reports_type);

            switch(true)
            {
                case strstr($reports_type, 'standard'):
                    $reports_info_query = db_query("select name, id, menu_icon,entities_id from app_reports where id='" . $reports_id . "' and created_by='" . $this->user['id'] . "'");
                    if($reports_info = db_fetch_array($reports_info_query))
                    {
                        $menu_icon = (strlen($reports_info['menu_icon']) > 0 ? $reports_info['menu_icon'] : 'fa-reorder');
                        $sub_menu[] = array('title' => $reports_info['name'],  'reports_id' => $reports_info['id'], 'entities_id' => $reports_info['entities_id'], 'class' => $menu_icon);
                    }
                    break;             
                case strstr($reports_type, 'common'):
                    
                    if($this->user['group_id']==0)
                    {
                        $reports_info_query = db_query("select r.id, r.name, r.menu_icon, r.entities_id from app_reports r, app_entities e where r.entities_id = e.id  and find_in_set(" . $this->user['group_id'] . ",r.users_groups) and r.reports_type = 'common'  and r.id='" . $reports_id . "'");
                    }
                    else
                    {
                        $reports_info_query = db_query("select r.id, r.name, r.menu_icon, r.entities_id from app_reports r, app_entities e, app_entities_access ea  where r.entities_id = e.id and e.id=ea.entities_id and length(ea.access_schema)>0 and ea.access_groups_id='" . db_input($this->user['group_id']) . "' and find_in_set(" . $this->user['group_id'] . ",r.users_groups) and r.reports_type = 'common'  and r.id='" . $reports_id . "'");
                    }
                    
                    if($reports_info = db_fetch_array($reports_info_query))
                    {
                        $menu_icon = (strlen($reports_info['menu_icon']) > 0 ? $reports_info['menu_icon'] : 'fa-reorder');
                        $sub_menu[] = array('title' => $reports_info['name'], 'reports_id' => $reports_info['id'],'entities_id' => $reports_info['entities_id'], 'class' => $menu_icon);
                    }
                    break;
            }
        }
        
        return $sub_menu;
    }
    
    function get_users_filters_panels()
    {
        $entity_id = (int)self::_post('entity_id');
        
        if(!isset_entity($entity_id))
        {
            api::response_error('Entity #' . $entity_id . ' does not exist');
        } 
        
        $fields_access_schema = users::get_fields_access_schema($entity_id, $this->user['group_id']);
        
        $panel_fields = [];
        $panels_query = db_query("select f.* from app_filters_panels f where (select count(*) from app_filters_panels_fields fp where fp.panels_id=f.id)>0  and f.type='' and (length(f.users_groups)=0 or find_in_set(" . $this->user['group_id'] . ",f.users_groups)) and f.is_active=1 and f.entities_id='" . $entity_id . "' order by f.sort_order");        
        while ($panels = db_fetch_array($panels_query))
        {
            $fields_query = db_query("select fp.*, f.type from app_filters_panels_fields fp, app_fields f where f.id=fp.fields_id  and fp.panels_id='" . $panels['id'] . "' order by fp.sort_order");
            while ($fields = db_fetch_array($fields_query))
            {
                if (isset($fields_access_schema[$fields['fields_id']]) and $fields_access_schema[$fields['fields_id']] == 'hide')
                {
                    continue;
                }
                
                $panel_fields[] = $fields;
            }
        }
        
        self::response_success($panel_fields);
    }
    
    function change_user_password()
    {
        $password = self::_post('new_password');
        
        if(strlen($password) < CFG_PASSWORD_MIN_LENGTH)
        {            
            self::response_error(TEXT_ERROR_PASSOWRD_LENGTH,'error_passowrd_length');
        }
        
        if(CFG_IS_STRONG_PASSWORD)
        {
            if(!preg_match('/[A-Z]/', $password) or !preg_match('/[0-9]/', $password) or !preg_match('/[^\w]/', $password))
            {                                
                self::response_error(TEXT_STRONG_PASSWORD_TIP,'error_passowrd_strong');
            }
        }
        
        $hasher = new PasswordHash(11, false);

        $sql_data = array();
        $sql_data['password'] = $hasher->HashPassword($password);

        db_perform('app_entity_1', $sql_data, 'update', "id='" . db_input($this->user['id']) . "'");
        
        self::response_success(TEXT_PASSWORD_UPDATED);
    }
}
