<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client as HttpClient;
use Spatie\Dropbox\TokenProvider;


class AutoRefreshingDropBoxTokenService implements TokenProvider
{
   private string $key;

   private string $secret;

   private string $refreshToken;

   public function __construct($key, $secret, $refreshToken)
   {
       $this->key = $key;
       $this->secret = $secret;
       $this->refreshToken = $refreshToken;
   }

   public function getToken(): string
   {
       return $this->refreshToken();
//        return Cache::remember('access_token', 14000, function () {
//            return $this->refreshToken();
//        });
   }

   public function refreshToken(): string|bool
   {
       try {
           $client = new HttpClient();
           $res = $client->request(
               'POST',
               "https://{$this->key}:{$this->secret}@api.dropbox.com/oauth2/token",
               [
                   'form_params' => [
                       'grant_type' => 'refresh_token',
                       'refresh_token' => $this->refreshToken,
                   ],
               ]
           );

           if ($res->getStatusCode() == 200) {
               $response = json_decode($res->getBody(), true);

               return trim(json_encode($response['access_token']), '"');
           } else {
               return false;
           }
       } catch (Exception $e) {
//            ray("[{$e->getCode()}] {$e->getMessage()}");

           return false;
       }
   }
}
