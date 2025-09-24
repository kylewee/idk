<?php
    namespace Dropbox;
    
    use Dropbox\Dropbox\Auth;
    use Dropbox\Dropbox\Files;
    use Dropbox\Dropbox\FileProperties;
    use Dropbox\Dropbox\FileRequests;
    use Dropbox\Dropbox\Paper;
    use Dropbox\Dropbox\Misc;
    use Dropbox\Dropbox\Sharing;
    use Dropbox\Dropbox\Users;

    class Dropbox {
        private static $token, $app_key, $app_secret;
        public $auth;
        public $files;
        public $file_properties;
        public $file_requests;
        public $paper;
        public $misc;
        public $sharing;
        public $users;
        
        public function __construct($accesstoken, $app_key,$app_secret) {
            self::$token = $accesstoken;
            $this->auth = new Auth();
            $this->files = new Files();
            $this->file_properties = new FileProperties();
            $this->file_requests = new FileRequests();
            $this->paper = new Paper();
            $this->misc = new Misc();
            $this->sharing = new Sharing();
            $this->users = new Users();
            self::$app_key = $app_key;
            self::$app_secret = $app_secret;
            
            self::refresh_token();
        }
        
        /*
        * Main function for handling post requests.
        */
        public static function postRequest($endpoint, $headers, $data, $json = TRUE) {
            $ch = curl_init($endpoint);
            array_push($headers, "Authorization: Bearer " . self::$token);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $r = curl_exec($ch);
            curl_close($ch);
            if ($json)
                return json_decode($r, true);
            else
                return $r;
        }
        
        static function refresh_token()
        {
            //$app_key = 'a3r9pg1z3omoc5y';
            //$app_secret = 'barxuxeks3txvj6';
            //$app_code = 'lodpzsWYFPgAAAAAAAAAYlkotgBK05uHam6xtM894ns';
            
            //$refresh_token = 'OvBT5n4pe3AAAAAAAAAAAR-XhOd8fu2cekiNPWwARl1BKR-bejn6iiYBDdqatwbX';;
                                        
            //self::oauth1Request('https://api.dropbox.com/oauth2/token?code=' . $app_code . '&grant_type=authorization_code',[],$app_key,$app_secret);
            
            $result = self::oauth1Request('https://api.dropbox.com/oauth2/token?grant_type=refresh_token&refresh_token=' . self::$token,[],self::$app_key,self::$app_secret);
           
            if(isset($result['error']))
            {
                print_rr($result);                
                exit();
            }
            else
            {
                self::$token = $result['access_token'];
            }
            
        }
        
        /*
        * Special case function for handling the from_oauth1 request
        */
        public static function oauth1Request($endpoint, $data, $app_key, $app_secret) {
            $ch = curl_init($endpoint);
            $headers = array("Content-Type: application/json");
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_USERPWD, "$app_key:$app_secret");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $r = curl_exec($ch);
            curl_close($ch);
                                    
            return  json_decode($r, true);            
        }
        
        /*
        * Updates the access token.
        */
        public function updateAccessToken($accesstoken) {
            self::$token = $accesstoken;
        }
    }

?>