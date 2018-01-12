<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use AppBundle\Entity\Users;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use AppBundle\Entity\Surveys;
use AppBundle\Entity\SurveyUsers;
use AppBundle\Entity\SurveyUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Models\RegistrationForm;
use AppBundle\Models\Register;
use AppBundle\Models\MarkingApiConnector;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use AppBundle\Models\Mailer;

class DefaultController extends Controller
{
	private $principal_survey_id;
	private $teacher_survey_id;
	private $nts_survey_id;
    /**
     * @Route("/land", name="landing_page")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }
    /**
     * @Route("/get-started", name="tool-work")
     */
    public function schoolworkAction(){
    	return $this->render('default/getstarted.html.twig');
    }
    /**
     * @Route("/development", name="development")
     */
    public function developmentAction(){
    	return $this->render('default/development.html.twig');
    }
    /**
     * @Route("/what-it-covers", name="covers")
     */
    public function whatCoversAction(){
    	return $this->render('default/what-it-covers.html.twig');
    }
    /**
     * @Route("/about-school-practices", name="school-practices")
     */
    public function schoolpracticesAction(){
    	return $this->render('default/schoolpractices.html.twig');
    }
    /**
     * @Route("/about-teaching-practices", name="teaching-practices")
     */
    public function teachingpracticesAction(){
    	return $this->render('default/teachingpractices.html.twig');
    }

    /**
     * @Route("/how-to-read-reports", name="read-reports")
     */
    public function readreportAction(){
    	return $this->render('default/readreports.html.twig');
    }
    /**
     * @Route("/how-to-use-reports", name="use-reports")
     */
    public function usereportAction(){
    	return $this->render('default/usereports.html.twig');
    }
    /**
     * @Route("/terms-and-conditions", name="terms")
     */
    public function termsAction(){
    	return $this->render('default/terms.html.twig');
    }
    /**
     * @Route("/for-kahui", name="kahui")
     */
    public function kahuiAction(){
    	return $this->render('default/kahui.html.twig');
    }

    /**
     * @Route("/stpreport/{type}/{pid}/{tid}", name="stp")
     */
    public function stpreportAction($type,$pid,$tid){
    	$sess = $this->get('session');
    	if(!$sess->has('token')){
    		$this->addFlash('error', 'No token');
    		return $this->redirectToRoute('login');
    	}
    	$uname = $sess->get('uname');
    	$cl = new Client();
    	$report_url = $this->getParameter('report_url');
    	//$url = 'http://myreporting.loc/r-script/stpTP/'.$tid.'/'.$pid;
    	$url = $report_url.'/r-script/'.$type.'/'.$tid.'/'.$pid;
    	//if($type != 'stpTP')$url .= '/'.$nts_id;
    	$mykey = 'Nzc3r20!7'.date('Y-m-d');
    	$encrypted_uname = Crypto::encryptWithPassword($uname,$mykey);
    	$res = $cl->request('GET',$url,[
    		'headers'=>[
    			//'X-AUTH-TOKEN'=>'admin777'
    			'X-AUTH-TOKEN'=>$encrypted_uname
    		]
    	]);
    	$ret = json_decode($res->getBody(),true);
    	$fn = $ret['filename'];
    	if($fn == '') return $this->render('default/stp.html.twig');
    	$url = $report_url.'/rscripts/view/'.$fn;
    	return $this->redirect($url,302);
    }
    /**
     * @Route("/getreport", name="stp1")
     */
    public function getreportAction(){
    	return $this->render('default/stp.html.twig');
    }
    /**
     * @Route("/", name="homepage")
     */
    public function testlandingpageAction(){
    	return $this->render('default/home.html.twig');
    }
    /**
     * @Route("/getschool/{schl_id}", name="school")
     */
    public function schoolAction($schl_id){
    	$ite_admin = $this->getParameter('iteadmin');
    	$ite_pw = $this->getParameter('itepass');
    	$tokenObj = $this->requestTokenFromMs($ite_admin, $ite_pw);
    	$token = $tokenObj->access_token;
    	$arr = array('moe'=>$schl_id);
    	$respObj = $this->requestToMarkingService($arr, 'school',$token);
    	//print_r($respObj);
    	$resp = new JsonResponse();
    	$resp->setStatusCode(200);
    	$resp->setData($respObj);
    	//$resp->send();
    	return $resp;

    }
    /**
     * @Route("/dashboard", name="dash")
     */
    public function dashAction(){
    	$sess = $this->get('session');
    	if(!$sess->has('token')){
    		return $this->redirectToRoute('login');
    	}
    	$token = $sess->get('token');
    	$schl_id = $sess->get('schl_id');
    	$roles = $sess->get('roles');
    	$email = $sess->get('email');
    	$res = $this->requestToMarkingService(array(), 'resource',$token);
    	$em = $this->getDoctrine()->getManager();
    	$surveys = $em->getRepository('AppBundle\Entity\Surveys')->getSurveys($schl_id);
    	$res = array();
    	foreach ($surveys as $s){
    	    $calYr = $s['cal_year'];
	    	$aid = $s['ms_id'];
	    	$form_params = array(
	    		'asset_id'=>$aid,
	    		'type'=>'assess',
	    		'student_type'=>5 //stp learner
	    	);
	    	$lbl = '';
	    	if($s['test_id'] == 73 && $s['cal_year']==date('Y')){
	    		$lbl = 'Teacher Survey';
	    		$tid = $s['ms_id'];
	    	}
	    	if($s['test_id'] == 74 && $s['cal_year']==date('Y')){
	    		$lbl = 'Principal Survey';
	    		$pid = $s['ms_id'];
	    		if(in_array(16, $roles)){
	    			$s_user = $em->getRepository('AppBundle\Entity\SurveyUser')->findOneBy(array('ms_id'=>$aid,'email'=>$email));

	    			$s['token'] = $s_user->getToken();
	    		}
	    	}
// 	    	if($s['test_id'] == 75){
// 	    		$lbl = 'STP Non-teaching School Leader Survey';
// 	    		$ntsid = $s['ms_id'];
// 	    	}
	    	$s['label']= $lbl;
	    	$action = 'listlearners';
	    	$l = $this->requestToMarkingService($form_params, 'listlearners');
	    	$s['total_count'] = count($l->students);
	    	$s['withdata'] = 0;
	    	$s['nodata'] = 0;
	    	foreach ($l->students as $st){
	    		if($st->response != ''){
	    			$s['withdata']++;
	    		}else{
	    			$s['nodata']++;
	    		}
	    	}
	    	$res[$calYr][] = $s;
    	}
    	return $this->render('default/dash.html.twig',array('isdash'=>true,'data'=>$res,'yr'=>date('Y')));
    }
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $req){
    	$form = $this->createFormBuilder()
    		->add('email',EmailType::class,array('label'=>'Email*','constraints'=>array(
    									new Email(array('message'=>'Incorrect Email Format'))),'attr'=>array(
    									'placeholder'=>'Email*','class'=>'form-control')))
    		->add('pw',PasswordType::class,array('label'=>'Password','attr'=>array(
    									'placeholder'=>'Password*','class'=>'form-control')))
    		->add('login',SubmitType::class,array('label'=>'Login','attr'=>array('class'=>'btn btn-danger btn-block')))
    		->getForm();
    		$form->handleRequest($req);
    		if($form->isSubmitted() && $form->isValid()){
    			$em = $this->getDoctrine()->getManager();
    			$fd = $form->getData();
    			$email = $fd['email'];
    			$pw = $fd['pw'];
    			//Check that email exists in the stp_users DB
    			$stp_user = $em->getRepository('AppBundle\Entity\Users')->findOneBy(array('email'=>$email));
    			if($stp_user==null){
    				$this->addFlash('error', 'Invalid User');
    			}else{
    				$uname = $stp_user->getUsername();
    				try{
    					$tokenObj = $this->requestTokenFromMs($uname, $pw);
    				}catch(ClientException $c){
    					$me =  json_decode($c->getResponseBodySummary($c->getResponse()));
    					$err = $me->error_description;
    					$this->addFlash('error', $err);
    					return $this->redirectToRoute('login');
    				}
    				$this->setSessionsFromToken($tokenObj,$stp_user->getRoles(),$email);
    				return $this->redirectToRoute('dash');
    			}
    		}
    	return $this->render('default/login.html.twig',array('form'=>$form->createView()));
    }
    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $req){
    	$sess = $req->getSession();
    	$sess->clear('token');
    	$sess->clear('expires');
    	$sess->clear('uname');
    	$sess->clear('schl_id');
    	$sess->invalidate();
    	return $this->redirectToRoute('homepage');
    }
    /**
     * @Route("/questions-to-ask", name="questions-to-ask")
     */
    public function qToAskAction(){
        return $this->render('default/qToAsk.html.twig');
    }
    /**
     * @Route("/register", name="reg")
     */
    public function registerAction(Request $req, MarkingApiConnector $conn,Register $register){
        $schools = $this->getschoolList($conn);
        // temp mesure to close off registeration
        //return $this->render('default/home.html.twig');
    	$em = $this->getDoctrine()->getManager();
    	//$form = $this->createRegistrationForm();
    	$form = $this->createForm(RegistrationForm::class,array(),array('schools'=>$schools));
    	$form->handleRequest($req);
    	if($form->isSubmitted() && $form->isValid()){
    		$fd = $form->getData();
    		//$r = new Register($em,$conn,$sess,$mail);
    		$r = $register;
    		try {
    		    $ret = $r->registerSchool($fd);
    		    if($ret){
    		        $this->addFlash('error', 'Registration complete. Please check your emails for further instructions.');
    		        return $this->redirectToRoute('homepage');
    		    }else{
    		        $this->addFlash('error', 'There was an error with your registration');
    		        return $this->render('default/register.html.twig',array('form'=>$form->createView()));
    		    }
    		} catch (\Exception $e) {
    		    $this->addFlash('error', $e->getMessage());
    		}
    	}
    	return $this->render('default/register.html.twig',array('form'=>$form->createView()));
    }

    /**
     * @Route("/registersurvey/{type}/{t_id}", name="reg_survey_user")
     */
    public function registersurveyAction($type,$t_id,Request $req){
        return $this->render('default/home.html.twig');
    	$marking_url = $this->getParameter('marking_url');
    	$em = $this->getDoctrine()->getManager();
    	$surveyLbl = 'Teacher';
    	$nameLbl = 'teacher';
    	$surveyMsId = 73;
    	$tresp = array();
    	if($type=='nts'){
    		$surveyLbl = 'Non-teaching School Leader';
    		$surveyMsId = 75;
    	}
    	$form = $this->createFormBuilder()
    		->add('t_email',RepeatedType::class,array(
    			'type'=>EmailType::class,
    			'invalid_message'=>$surveyLbl.' email fields must match',
    			'first_options'=>array('label'=>'Email*','constraints'=>array(
    				new Email(array('message'=>'Incorrect Email Format'))
    			),'attr'=>array('placeholder'=>'Email*','class'=>'form-control')),
    			'second_options'=>array('label'=>'Confirm Email*','attr'=>array('placeholder'=>'Confirm Email*','class'=>'form-control'))))
    		->add('register',SubmitType::class,array('label'=>'Register','attr'=>array('class'=>'btn btn-danger btn-block')))
    		->add('forget',SubmitType::class,array('label'=>'Forgot Password','attr'=>array('class'=>'btn btn-danger btn-block')))
    		->getForm();
    	$form->handleRequest($req);
    	//Check that this t_id exists in DB
    	$survey = $em->getRepository('AppBundle\Entity\Surveys')->findOneBy(array('ms_id'=>$t_id,'test_id'=>$surveyMsId));
    	$moe = $survey->getMoeId();
    	if($survey == null){
    		$this->addFlash('error', 'Invalid Survey');
    		$form->remove('register');
    		$form->add('register',SubmitType::class,array('label'=>'Register','disabled'=>true,'attr'=>array('class'=>'btn btn-danger btn-block')));
    		return $this->render('default/register_survey_user.html.twig',array('form'=>$form->createView(),'lbl'=>$surveyLbl));
    	}
    	if($form->isSubmitted() && $form->isValid()){
    		$fd = $form->getData();
    		$t_email = $fd['t_email'];
    		//Check if email already exists in DB for this id
    		$s_user = $em->getRepository('AppBundle\Entity\SurveyUser')->findOneBy(array('ms_id'=>$t_id,'email'=>$t_email));
    		if($form->get('register')->isClicked()){
	    		if($s_user!=null){
	    			$this->addFlash('error', 'User has already registered for this survey before. Please use a different email.');
	    		}else{

	    			//	Add user to marking service and get token
	    			$tresp = $this->addUserToSurvey($t_email, $t_id, $nameLbl,$moe);
	    			$teacher = $tresp->students[0];
	    			//$teacher->token = 'asdasd';
	    			//Add user in DB
	    			$s_user = new SurveyUser();
	    			$s_user->setEmail($t_email);
	    			$s_user->setMsId($t_id);
	    			$s_user->setToken($teacher->token);
	    			$em->persist($s_user);
	    			$em->flush();
	    			$this->sendEmailToUser($t_email,$teacher->token, $surveyLbl);
	    			$this->addFlash('success', 'Password has been sent to your email.');
	    		}
    		}else if($form->get('forget')->isClicked()){
    			if($s_user==null){
    				$this->addFlash('error', 'User has not registered for this survey');
    			}else{
    				$this->sendEmailToUser($t_email, $s_user->getToken(), $surveyLbl);
    				$this->addFlash('success', 'Token has been resent to your email.');
    			}
    		}
    	}

    	return $this->render('default/register_survey_user.html.twig',array('form'=>$form->createView(),'lbl'=>$surveyLbl));
    }
	/**
	 * @param client
	 */
	 private function addPrincipalToPrincipalSurvey($client,$token,$principalArr) {
	 	$marking_url = $this->getParameter('marking_url');

	 	$form_params = array(
	 		'assess_id'=>$this->principal_survey_id,
	 		'students'=>array($principalArr)
	 	);
	 	$pRes = $this->requestToMarkingService($form_params, 'addlearnertoassessment');
		$pObj = $pRes->students[0];
		//$p_id = $pObj->id;
		return $pObj;
	}
	private function addUserToSurvey($email,$s_id,$lbl,$schl_id){
		$ite_admin = $this->getParameter('iteadmin');
		$ite_pw = $this->getParameter('itepass');
		$tokenObj = $this->requestTokenFromMs($ite_admin, $ite_pw);
		$token = $tokenObj->access_token;
		$ako = $this->requestToMarkingService(array('moe'=>$schl_id), 'school',$token);
		$school = $ako->school;
		$school_name = $school->name;
		$userArr = array(
			'user_type'=>5,	//stp user
			'student_fname'=>'a '.$lbl.' from',
			'student_lname'=>$school_name,
			'year_level'=>0,
			'email'=>$email
		);
		$form_params = array(
			'assess_id'=>$s_id,
			'students'=>array($userArr),
			'schl_id'=>$schl_id
		);
		$resp = $this->requestToMarkingService($form_params, 'addlearnertoassessment',$token);
		return $resp;
	}

    private function sendEmailToUser($email,$token,$lbl){
    	$basesur_url = $this->getParameter('survey_url');
    	$survey_url = $this->getParameter('survey_url');
    	$survey_url.='/quiz_backbone?token='.$token;
    	$msg = \Swift_Message::newInstance()
    	->setSubject('TSP Teacher welcome and survey')
    	->setFrom('tspsurveys@nzcer.org.nz')
    	->setTo($email)
    	//->setBcc('assessmentservices@nzcer.org.nz')
    	->setBody($this->renderView('Email/survey_user_email.html.twig',array('sur_url'=>$basesur_url, 'url'=>$survey_url,'token'=>$token,'lbl'=>$lbl)),'text/html');
    	$this->get('mailer')->send($msg);
    }

    private function requestTokenFromMs($uname,$pw){
    	$marking_url = $this->getParameter('marking_url');
    	$client = new Client();
    	$response = $client->request('POST',$marking_url.'/front.php?req=api/index',[
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

    }
    private function requestToMarkingService($form_params,$api_action,$token=null){
    	$marking_url = $this->getParameter('marking_url');
    	if($token==null){
    		$sess = $this->get('session');
    		$token = $sess->get('token');
    	}
    	$client = new Client();
    	$url = $marking_url.'/front.php?req=api/'.$api_action;
    	$resp = $client->request('POST',$url,[
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
    private function setSessionsFromToken($tokenObj,$roles,$email){
    	$client = new Client();
    	$this->marking_url = $this->getParameter('marking_url');
    	$access_token = $tokenObj->access_token;
    	$this->token = $access_token;
    	//$sess = $req->getSession();
    	$sess = $this->get('session');
    	//$sess->start();
    	$todaysec = strtotime('now');
    	$expire_date = $todaysec+$tokenObj->expires_in;
    	$sess->set('token',$access_token);
    	$sess->set('expires',$expire_date);
    	$response = $client->request('POST',$this->marking_url.'/front.php?req=api/resource',[
    		'headers' => [
    			'Authorization' => 'Bearer '.$access_token
    		],
    		'allow_redirects' => [
    			'strict'=>true
    		]
    	]);
    	$jsonResp = json_decode($response->getBody());
    	//print_r($jsonResp);
    	//exit();
    	//echo 'my access token is: '.$access_token;
    	//exit();
    	$t = $jsonResp->token;
    	$sess->set('uname',$t->user_name);
    	$sess->set('schl_id',$t->schl_id);
    	$sess->set('ufname',$t->user_fname);
    	$sess->set('schl_name',$t->schl_name);
    	$sess->set('roles',$roles);
    	$sess->set('email',$email);
    	//print_r($sess);
    	return true;
    }
    private function getschoolList(MarkingApiConnector $conn){
    	$ite_admin = $this->getParameter('iteadmin');
    	$ite_pw = $this->getParameter('itepass');
    	//$tokenObj = $this->requestTokenFromMs($ite_admin, $ite_pw);
    	$tokenObj = $conn->requestTokenFromMs($ite_admin, $ite_pw);
    	$token = $tokenObj->access_token;
    	$arr = array('moe'=>'');
    	//$respObj = $this->requestToMarkingService($arr, 'school',$token);
    	$respObj = $conn->requestToMarkingService($arr, 'school',$token);
    	$schools = $respObj->school;
    	$mySchools = array();
    	foreach ($schools as $s){
    		$mySchools[$s->name.' --- '.$s->id] = $s->id;
    	}
    	return $mySchools;
    }
	public function getPrincipalSurveyId() {
		return $this->principal_survey_id;
	}
	public function setPrincipalSurveyId($principal_survey_id) {
		$this->principal_survey_id = $principal_survey_id;
		return $this;
	}
	public function getTeacherSurveyId() {
		return $this->teacher_survey_id;
	}
	public function setTeacherSurveyId($teacher_survey_id) {
		$this->teacher_survey_id = $teacher_survey_id;
		return $this;
	}
	public function getNtsSurveyId() {
		return $this->nts_survey_id;
	}
	public function setNtsSurveyId($nts_survey_id) {
		$this->nts_survey_id = $nts_survey_id;
		return $this;
	}
	/**
	 * @Route("/test/service", name="testservice")
	 */
	public function testserviceAction(Register $reg){
	    dump($reg);
	    $superuser_name = $this->getParameter('iteadmin');
	    $superuser_pw = $this->getParameter('itepass');
	    return new Response('Done '.$superuser_name.' '.$superuser_pw);
	}

}
