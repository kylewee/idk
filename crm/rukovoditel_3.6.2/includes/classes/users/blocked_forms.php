<?php

class blocked_forms
{
    static function is_nabled()
    {
        return (CFG_BLOCK_RECORD_FORM_WHEN_EDITING==1 and CFG_AUTO_LOGOUT_INACTION_USERS==1) ? true : false;
    }
    
    static function reset()
    {
        global $app_user;
        
        if(IS_AJAX or !self::is_nabled()) return false;
            
        db_query("delete from app_blocked_forms where user_id='{$app_user['id']}'"); 
    }
    
    static function remove_inaction_users()
    {
        if(!self::is_nabled()) return false;
        
        db_query("delete from app_blocked_forms where user_id not in (select users_id from app_last_user_action)");         
    }
    
    static function unset(int $entity_id,int  $item_id)
    {
        global $app_user;
        
        db_query("delete from app_blocked_forms where user_id='{$app_user['id']}' and entity_id={$entity_id} and item_id={$item_id}");
    }
    
    static function set(int $entity_id,int  $item_id)
    {
        global $app_user;
        
        $check_query = db_query("select id from app_blocked_forms where user_id='{$app_user['id']}' and entity_id={$entity_id} and item_id={$item_id}");
        if($check = db_fetch_array($check_query))
        {
            db_query("update app_blocked_forms set date=" . time() . " where id='{$check['id']}'"); 
        }
        else
        {
            $sql_data = [
                'entity_id' => $entity_id,
                'item_id' => $item_id,
                'user_id' => $app_user['id'],
                'date' => time(),
            ];
            
            db_perform('app_blocked_forms', $sql_data);
        }
    }
    
    static function is(int $entity_id,int  $item_id)
    {
        global $app_user;
        
        $check_query = db_query("select user_id from app_blocked_forms where user_id!='{$app_user['id']}' and entity_id={$entity_id} and item_id={$item_id}");
        if($check = db_fetch_array($check_query))
        {
            return $check['user_id'];
        }
        else
        {
            return false;
        }
    }
    
    static function validate(int $entity_id,int  $item_id)
    {
        global $app_users_cache;
        
        if(!self::is_nabled() or !$item_id) return false;
        
        if($user_id = self::is($entity_id, $item_id))
        {
            $msg = strlen(CFG_BLOCK_RECORD_FORM_WARNIGN_TEXT) ? CFG_BLOCK_RECORD_FORM_WARNIGN_TEXT : TEXT_FORM_BLOCKED_WARNING;
            $html  = ajax_modal_template_header(TEXT_WARNING) . '<div class="modal-body">' . alert_warning(str_replace('[user_name]', $app_users_cache[$user_id]['name']??'', nl2br($msg) )). '</div>' . ajax_modal_template_footer('hide-save-button');
            echo $html;
            app_exit();
        }
        else
        {
            self::set($entity_id, $item_id);
        }
    }
    
    static function render_form_js(int $entity_id,int  $item_id)
    {
        if(self::is_nabled() and $item_id>0)
        {        
            $html = '
                <script>
                     $(function(){
                        $("#ajax-modal").on("hide.bs.modal", function (e) {
                            $.ajax({url: "' . url_for('dashboard/dashboard','action=blocked_forms_unset&entity_id=' . $entity_id . '&item_id=' . $item_id) . '"})

                            window.onbeforeunload = ""                            
                        })                                        
                     }); 
                </script>
                ';
        }
        else
        {
            $html = '
                <script>
                     $(function(){
                        $("#ajax-modal").on("hide.bs.modal", function (e) {                            
                            window.onbeforeunload = ""                            
                        })                                        
                     }); 
                </script>
                ';
        }
        
        return $html;
    }
}
