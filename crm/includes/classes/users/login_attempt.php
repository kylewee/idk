<?php


class login_attempt
{
    static function is_enabled()
    {
        return (CFG_ENABLE_LOGIN_ATTEMPT and !self::is_trusted_ip()) ? true:false;
    }
    
    static function is_trusted_ip()
    {                                                                
        return (strlen(CFG_LOGIN_ATTEMPT_TRUSTED_IP) and in_array(get_user_ip(),array_map('trim',explode(',',CFG_LOGIN_ATTEMPT_TRUSTED_IP)))) ? true:false;
    }
    
    static function verify()
    {
        $user_ip = get_user_ip();
                        
        if(!self::is_enabled() or !strlen($user_ip)) return true;
        
        $check_query = db_query("select * from app_login_attempt where user_ip='" . db_input($user_ip) . "' and is_banned=1");
        if($check = db_fetch_array($check_query))
        {
            if(($check['date_banned']+(CFG_NUMBER_MINUTES_IP_BLOCKED*60))>=time())
            {
                return false;
            }
            else
            {
                self::reset();
            }
        }
        
        return true;
    }
    
    static function set()
    {
        $user_ip = get_user_ip();
                        
        if(!self::is_enabled() or !strlen($user_ip)) return true;
        
        $check_query = db_query("select * from app_login_attempt where user_ip='" . db_input($user_ip) . "'");
        if($check = db_fetch_array($check_query))
        {
            if(($check['count_attempt']+1)>=CFG_NUMBER_LOGIN_ATTEMPTS)
            {
                db_query("update app_login_attempt set count_attempt=count_attempt+1, is_banned=1, date_banned=" . time() . " where id=" . $check['id']);
            }
            else
            {
                db_query("update app_login_attempt set count_attempt=count_attempt+1 where id=" . $check['id']);
            }
        }
        else
        {
            $sql_data = [                
                'user_ip' => $user_ip,
                'count_attempt' => 1,
            ];

            db_perform('app_login_attempt', $sql_data);
        }
    }
    
    static function reset()
    {
        $user_ip = get_user_ip();
                        
        if(!self::is_enabled() or !strlen($user_ip)) return true;
        
        db_query("delete from app_login_attempt where user_ip='" . db_input($user_ip) . "'");
    }

}
