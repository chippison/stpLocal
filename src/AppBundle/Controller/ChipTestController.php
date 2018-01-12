<?php
namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Models\MarkingApiConnector;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Models\Registration;
use AppBundle\Models\Mailer;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManager;
use AppBundle\Models\Register;
use Doctrine\ORM\EntityManagerInterface;

class ChipTestController extends Controller{
    /**
     * @Route("/connect", name="connect")
     */
    public function testMarkingApiConnectionAction(MarkingApiConnector $apiC,SessionInterface $s,Mailer $r){
//         dump($s);
//         $apiConnect = $apiC;
//         $myadmin = 'tsp777';
//         $pw = '123456';
//         $tokenObj = $apiConnect->requestTokenFromMs($myadmin, $pw);
//         //print_r($tokenObj);
//         dump($tokenObj);
//         $apiConnect->setSessionsFromToken($tokenObj, '15', 'chippison@gmail.com');
        dump($r);
        $r->sendEmailToAdministrator('chippison@gmail.com', 12345);
        return new Response('wala');
    }
    /**
     * @Route("/testregisterAdmin", name="registeradmin")
     */
    public function testRegisterAction(EntityManagerInterface $em, MarkingApiConnector $conn, SessionInterface $sess,Mailer $mail){
        $r = new Register($em, $conn, $sess,$mail);
        //$ako = $r->createAdministrator('chippison@wala', '777', 1, 'Chip', 'pison', 'Test1234');
        dump($sess);
        $surveys = $r->createAssessments(777,0);
        $p = $r->createPrincipalAndSendEmails('RotcivX', 'PisonDx', 'chippison@gmail.com', 'Test1234', 777, 1, 'chippison@gmail.com');
//         $pArr = array(
//             'student_fname' => 'RotcivD',
//             'student_lname' => 'PisonD',
//             'year_level'=>0,
//             'email'=>'chippison@gmail.com',
//             'user_type'=>5
//         );
//         $form_params = array(
//             'assess_id'=>755067,
//             'students'=>array($pArr),
//         );
//         echo json_encode($form_params);
//         $p = $conn->requestToMarkingService($form_params, 'addlearnertoassessment');
        dump($p);
        return new Response('wala');
    }
    /**
     * @Route("/pal", name="registeradmin1")
     */
    public function testPalindromeAction(){
        $word = 'Deleveled';
        $wordArr = str_split($word);
        $c = count($wordArr);
        $new_word = '';
        for($i=$c;$i>0;$i--)
        {
            dump($i);
            dump($wordArr[$i-1]);
            $new_word.=$wordArr[$i-1];
            dump($new_word);

        }
        $b=false;
        if(strtolower($new_word)==strtolower($word)) $b = true;
        dump($b);
        dump($wordArr[7]);
        dump($c);
        dump($new_word);
        return new Response($new_word);
    }
}