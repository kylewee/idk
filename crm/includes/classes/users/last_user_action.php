<?php


class last_user_action
{
    static function set()
    {
        global $app_user;
        
        if(CFG_AUTO_LOGOUT_INACTION_USERS==0) return false;
        
        $action_query = db_query("select id from app_last_user_action where users_id='{$app_user['id']}'");
        if($action = db_fetch_array($action_query))
        {
            db_query("update app_last_user_action set date=" . time() . " where users_id='{$app_user['id']}'"); 
        }
        else
        {
            $sql_data = [
                'users_id'=>$app_user['id'],
                'date'=>time(),
            ];
            
            db_perform('app_last_user_action', $sql_data);
        }
    }
    
    static function has()
    {
        global $app_user;
        
        $action_query = db_query("select id from app_last_user_action where users_id='{$app_user['id']}'");
        if($action = db_fetch_array($action_query))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    static function remove_inaction_users()
    {
        db_query("delete from app_last_user_action where date<=" . strtotime("-" . CFG_AUTO_LOGOUT_INACTION_TIME . " minutes"));
    }
    
    static function render_js()
    {
        if(CFG_AUTO_LOGOUT_INACTION_USERS==0) return '';
                        
        $html = '
            <script>
                 $(function(){
                    var app_has_user_action = false;
                    
                    setInterval(function(){
                        if(app_has_user_action)
                        {
                            $.ajax({url: "' . url_for('dashboard/dashboard','action=set_last_user_action') . '"})
                            app_has_user_action = false;
                        }
                        
                        $.ajax({url: "' . url_for('dashboard/dashboard','action=check_inaction_users') . '"}).done(function(response){                            
                            if(response=="INACTION")
                            {
                                window.onbeforeunload = ""
                                window.top.location.href = url_for("users/login","action=logoff")
                            }
                        })
                    },60000);   
                    
                    $("body").on( "mousemove", function( event ) {
                        app_has_user_action = true;                                                
                    })
                 }); 
            </script>
            ';
        
        self::set();
        
        return $html;
    }
}
