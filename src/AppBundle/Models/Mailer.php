<?php
namespace AppBundle\Models;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Templating\EngineInterface;

/**
 *
 *@author chippison
 * Date created: 24 Nov 2017
 **/
 class Mailer{
     private $url;
     private $mailer;
     private $templater;
     private $survey_url;
     function __construct($base_url,$s_url,\Swift_Mailer $mail,EngineInterface$template){
         $this->url = $base_url;
         $this->mailer = $mail;
         $this->templater = $template;
         $this->survey_url = $s_url;
     }
     public function sendEmailToAdministrator($a_email,$t_id){
         $msg = \Swift_Message::newInstance()
         ->setSubject('TSP Administrator instructions for Teacher Survey')
         ->setFrom('tspsurveys@nzcer.org.nz')
         ->setTo($a_email)
         ->setBody($this->templater->render('Email/admin_teacher_email.html.twig',array('tid'=>$t_id)),'text/html');
         return $this->mailer->send($msg);
     }
     public function sendEmailToUser($email,$token,$lbl){
         $urlWithToken = $this->survey_url.'/quiz_backbone?token='.$token;
         $msg = \Swift_Message::newInstance()
             ->setSubject('TSP Teacher welcome and survey')
             ->setFrom('tspsurveys@nzcer.org.nz')
             ->setTo($email)
             ->setBody($this->templater->render('Email/survey_user_email.html.twig',array('sur_url'=>$this->survey_url, 'url'=>$urlWithToken,'token'=>$token,'lbl'=>$lbl)),'text/html');
         return $this->mailer->send($msg);

     }
     public function sendEmailAdminAccessDetails($email,$pw){
         $msg = \Swift_Message::newInstance()
             ->setSubject('TSP Administration and reporting instructions')
             ->setFrom('tspsurveys@nzcer.org.nz')
             ->setTo($email)
             ->setBody($this->templater->render('Email/admin_welcome_email.html.twig',array('url'=>$this->url,'email'=>$email,'pw'=>$pw)),'text/html');
         return $this->mailer->send($msg);
     }
     public function sendEmailPrincipalAccessDetails($p_email,$p_pw,$token){
         $msg = \Swift_Message::newInstance()
             ->setSubject('TSP Administration and reporting instructions for Principals')
             ->setFrom('tspsurveys@nzcer.org.nz')
             ->setTo($p_email)
             ->setBody($this->templater->render('Email/principal_access_email.html.twig',array('url'=>$base_url,'email'=>$p_email,'pw'=>$p_pw,'token'=>$token)),'text/html');
         return $this->mailer->send($msg);
     }
     public function sendEmailToPrincipal($p_email,$p_token){
         $urlWithToken = $this->survey_url.'/quiz_backbone?token='.$p_token;
         $msg = \Swift_Message::newInstance()
             ->setSubject('TSP Principal welcome and survey')
             ->setFrom('tspsurveys@nzcer.org.nz')
             ->setTo($p_email)
             ->setBody($this->templater->render('Email/principal_email.html.twig',array('sur_url'=>$this->survey_url,'url'=>$urlWithToken,'token'=>$p_token)),'text/html');
         return $this->mailer->send($msg);
     }
 }