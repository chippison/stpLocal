<?php
namespace AppBundle\Models;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 *Model to request things from marking service
 *@author chippison
 * Date created: 24 Nov 2017
 **/
 class MarkingApiConnector{
     private $marking_url;
     private $client;
     private $session;
     function __construct($marking_url,$client, SessionInterface $sess){
         $this->marking_url = $marking_url;
         //$this->client = new Client();
         $this->client = $client;
         $this->session = $sess;
     }
     public function requestTokenFromMs($uname,$pw){
         try {
             $response = $this->client->request('POST',$this->marking_url.'/front.php?req=api/index',[
                 'form_params'=>[
                     'client_id'=>'stp',
                     'client_secret'=>'stpsecret',
                     'grant_type'=>'password',
                     'username'=>$uname,
                     'password'=>$pw
                 ],
                 'allow_redirects'=>[
                     'strict'=>true
                 ]
             ]);
             $tokenObj = json_decode($response->getBody());
             return $tokenObj;
         } catch (\Exception $e) {
             dump($e);
             dump($uname);
             dump($pw);
         }

     }
     public function requestToMarkingService($form_params,$api_action,$token=null){
         if($token == null) $token = $this->session->get('token');
         $url = $this->marking_url.'/front.php?req=api/'.$api_action;
         $resp = $this->client->request('POST',$url,[
             'headers'=>[
                 'Authorization'=>'Bearer '.$token
             ],
             'form_params'=>$form_params,
             'allow_redirects'=>[
                 'strict'=>true
             ]

         ]);
         $resObj = json_decode($resp->getBody());
         return $resObj;
     }
     /**TRANSFERRED this to Register.php
     public function setSessionsFromToken($tokenObj,$roles,$email){
         $access_token = $tokenObj->access_token;
         $todaysec = strtotime('now');
         $expire_date = $todaysec+$tokenObj->expires_in;
         $this->session->set('token',$access_token);
         $this->session->set('expires',$expire_date);
         //$response = $this->client->request('POST')
         //$form_params = arr
         $jsonResp = $this->requestToMarkingService(array(), 'resource',$access_token);
         $t = $jsonResp->token;
         $this->session->set('uname', $t->user_name);
         $this->session->set('schl_id',$t->schl_id);
         $this->session->set('ufname',$t->user_fname);
         $this->session->set('schl_name',$t->schl_name);
         $this->session->set('roles',$roles);
         $this->session->set('email',$email);
         return true;
     }**/
 }