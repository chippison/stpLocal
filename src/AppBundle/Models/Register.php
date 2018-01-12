<?php
namespace AppBundle\Models;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Users;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use AppBundle\Entity\Registration;
use AppBundle\Entity\Surveys;
use AppBundle\Entity\SurveyUser;
use Doctrine\ORM\EntityManagerInterface;

class Register
{
    private $em;
    private $conn;
    private $sess;
    private $p_surId;
    private $t_surId;
    private $mail;
    private $su_name;
    private $su_pass;
    function __construct(EntityManagerInterface $em, MarkingApiConnector $conn, SessionInterface $sess, Mailer $mail,$superuser_name,$superuser_pw){
        $this->em = $em;
        $this->conn = $conn;
        $this->sess = $sess;
        $this->mail = $mail;
        $this->su_name = $superuser_name;
        $this->su_pass = $superuser_pw;
    }
    public function registerSchool($form_data){
        //instantiate vars from form_data..
        //initially it is assumed that admin is the same as principal
        $admin_email = $form_data['email'];
        $admin_fname = $form_data['fname'];
        $admin_lname = $form_data['lname'];
        $admin_pw = $form_data['password'];
        $p_fname = $form_data['fname'];
        $p_lname = $form_data['lname'];
        $p_email = $form_data['email'];
        $pw = $form_data['password'];
        $moe = $form_data['moe'];
        if(count($form_data['cols'])>0){
            $cols = $form_data['cols'][0];
        }else{
            $cols = 0;
        }
        //if a_email is not null, then principal is not going to be the same person as admin
        if($form_data['a_email'] != null){
            $admin_email = $form_data['a_email'];
            $admin_fname = $form_data['a_fname'];
            $admin_lname = $form_data['a_lname'];
            $admin_pw = $form_data['a_password'];
            //if admin is different from principal, make sure their emails are not the same
            if($admin_email == $p_email){
                throw new \Exception('Principal Email and Administrator Email must not be the same.');
            }
        }
        //check that school is not registered for the current year
        $registration = $this->em->getRepository('AppBundle\Entity\Registration')->findOneBy(array('moe'=>$moe,'calYr'=>date('Y')));
        if($registration!=null) throw new \Exception('School has already registered for the current year');
        $registration = new Registration();
        $registration->setCalYr(date('Y'));
        $registration->setMoe($moe);
        $registration->setLastModified();
        $this->em->persist($registration);
        //$this->em->flush();
        //in 2018, we use email for their username instead of tsp+moe_id
        $uname = $admin_email;
        $isAdmin = $form_data['isadmin'];
        if($admin_email!=''){
            //create administrator and set sessions
            $token = $this->createAdministrator($admin_email, $moe, $isAdmin, $admin_fname, $admin_lname, $admin_pw);
            $surveys = $this->createAssessments($moe,$cols);
            //now create principal
            $this->createPrincipalAndSendEmails($p_fname, $p_lname, $p_email, $pw, $moe, $isAdmin, $admin_email);
            //now send admin emails
            $this->mail->sendEmailAdminAccessDetails($admin_email, $admin_pw);
            $this->mail->sendEmailToAdministrator($admin_email, $this->t_surId);
        }
        return true;

    }
    public function createPrincipalAndSendEmails($p_fname,$p_lname,$p_email,$pw,$moe,$isadmin,$admin_email){
        $pArr = array(
            'student_fname' => $p_fname,
            'student_lname' => $p_lname,
            'year_level'=>0,
            'email'=>$p_email
        );
        $pObj = $this->addPrincipalToSurvey($pArr);
        //dump($pObj);
        $p_survey_token = $pObj->token;
        //$p_survey_token = 'ABCDEF'; //for localhost, we cannot get tokens because it is not allowed;
        $this->mail->sendEmailToPrincipal($p_email, $p_survey_token);
        //Create TSP survey User for principal
        $sUser = new SurveyUser();
        $sUser->setEmail($p_email)
            ->setMsId($this->p_surId)
            ->setToken($p_survey_token);
        $this->em->persist($sUser);
        $this->em->flush();
        if($p_email!=$admin_email && $isadmin == 0){
            $arr = array(
                'uname'=>$p_email,
                'fname'=>$p_fname,
                'lname'=>$p_lname,
                'email'=>$p_email,
                'pass'=>$pw,
                'org_id'=>$moe,
                'role'=>16,
                'type'=>3
            );
            $this->createStpPrincipalUser($arr);
            $this->mail->sendEmailPrincipalAccessDetails($p_email, $pw, $p_survey_token);
            $pUser = new Users();
            $pUser->setEmail($p_email)
                ->setMoeId($moe)
                ->setUsername($p_email)
                ->setRoles(16);
            $this->em->persist($pUser);
            $this->em->flush();
        }

    }
    private function createStpPrincipalUser($pArr){
        $iteAdmin = $this->su_name;
        $itePw = $this->su_pass;
        $tokenObj = $this->conn->requestTokenFromMs($iteAdmin, $itePw);
        $token = $tokenObj->access_token;
        $respObj = $this->conn->requestToMarkingService($pArr, 'users',$token);

    }
    private function addPrincipalToSurvey($principalArr){
        $principalArr['user_type'] = 5;
        $form_params = array(
            'assess_id'=>$this->p_surId,
            'students'=>array($principalArr)
        );
        $pRes = $this->conn->requestToMarkingService($form_params, 'addlearnertoassessment');
        $pObj = $pRes->students[0];
        return $pObj;
    }
    public function createAssessments($moe,$cols){
        $assessArrs = array();
        $tspTeacherSurvey = array(
            'name'=>'Teacher Survey',
            'test_id'=>73,
            'year'=>4,
            'ref_year'=>4,
            'term'=>1
        );
        $tspPrincipalSurvey = array(
            'name'=>'Principal Survey',
            'test_id'=>74,
            'year'=>4,
            'ref_year'=>4,
            'term'=>1
        );
        $assessArrs[] = $tspTeacherSurvey;
        $assessArrs[] = $tspPrincipalSurvey;
        $form_params = array(
            'school_id'=>$moe,
            'assessments'=>$assessArrs
        );
        $respObj = $this->conn->requestToMarkingService($form_params, 'assessment');
        $surveys = $respObj->assessments;
        $this->addToTspSurveyDb($moe,$surveys,$cols);
        return $surveys;
    }
    public function createAdministrator($admin_email,$moe,$isAdmin,$admin_fname,$admin_lname,$admin_pw){
        //check that this email is only used once for registration as this will be the username of user
        $users = $this->em->getRepository('AppBundle\Entity\Users')->findBy(array('email'=>$admin_email));
        if(count($users)>0){
            foreach ($users as $u){
                if($u->getMoeId() != $moe)throw new \Exception('This email has been used for registration on a different school');
            }
        }
        $admin_user = $this->em->getRepository('AppBundle\Entity\Users')->findOneBy(array('email'=>$admin_email,'moe_id'=>$moe));
        if($admin_user == null){
            $admin_user = new Users();
        }
        $adminRole = 15;
        if($isAdmin == 1){
            $adminRole = '15|16';
        }
        $adminArr = array(
            'uname'=>$admin_email,
            'fname'=>$admin_fname,
            'lname'=>$admin_lname,
            'email'=>$admin_email,
            'pass'=>$admin_pw,
            'org_id'=>$moe,
            'role'=>$adminRole,
        );
        //update admin user
        $admin_user->setEmail($admin_email)
        ->setUsername($admin_email)
        ->setMoeId($moe)
        ->setRoles($adminRole);
        $this->em->persist($admin_user);
        $token = $this->createStpAdminInMs($adminArr);
        $this->em->flush();
        return $token;

    }
    /**
     * Create new Admin User in Marking Service
     * & set session tokens
     * @return token to use for
     */
    private function createStpAdminInMs($adminArr){
        //extract($adminArr);
        $adminArr['type'] = 3; //user_type in marking service
        $ite_admin = $this->su_name;
        $ite_pw = $this->su_pass;
        $tokenObj = $this->conn->requestTokenFromMs($ite_admin, $ite_pw);
        $token = $tokenObj->access_token;
        //create new user in marking service
        $respObj = $this->conn->requestToMarkingService($adminArr, 'users',$token);
        //now login with newly created user
        $tokenObj = $this->conn->requestTokenFromMs($adminArr['uname'], $adminArr['pass']);
        $rolesArr = explode('|', $adminArr['role']);
        $this->setSessionsFromToken($tokenObj, $rolesArr, $adminArr['email']);
        return $respObj;
    }
    private function setSessionsFromToken($tokenObj,$roles,$email){
        $access_token = $tokenObj->access_token;
        $todaysec = strtotime('now');
        $expire_date = $todaysec+$tokenObj->expires_in;
        $this->sess->set('token', $access_token);
        $this->sess->set('expires', $expire_date);
        $jsonResp = $this->conn->requestToMarkingService(array(), 'resource',$access_token);
        $t = $jsonResp->token;
        $this->sess->set('uname', $t->user_name);
        $this->sess->set('schl_id', $t->schl_id);
        $this->sess->set('ufname', $t->user_fname);
        $this->sess->set('schl_name', $t->schl_name);
        $this->sess->set('roles', $roles);
        $this->sess->set('email', $email);
        return true;
    }
    private function addToTspSurveyDb($moe,$surveys,$cols){
        foreach ($surveys as $s){
            $calYr = date('Y');
            $sur = $this->em->getRepository('AppBundle\Entity\Surveys')->findOneBy(array('cal_year'=>$calYr,'moe_id'=>$moe,'test_id'=>$s->test_id));
            if($sur == null) $sur = new Surveys();
            $sur->setCalYear($calYr)
                ->setMoeId($moe)
                ->setTestId($s->test_id)
                ->setMsId($s->id)
                ->setCols($cols);
            $this->em->persist($sur);
            if($s->test_id == 73) $this->setA_surId($s->id);
            if($s->test_id == 74) $this->setP_surId($s->id);
        }
        $this->em->flush();
    }
    /**
     * @return mixed
     */
    private function getP_surId()
    {
        return $this->p_surId;
    }

    /**
     * @return mixed
     */
    private function getA_surId()
    {
        return $this->t_surId;
    }

    /**
     * @param mixed $p_surId
     */
    private function setP_surId($p_surId)
    {
        $this->p_surId = $p_surId;
    }

    /**
     * @param mixed $a_surId
     */
    private function setA_surId($a_surId)
    {
        $this->t_surId = $a_surId;
    }

}

