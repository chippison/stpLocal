<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
	/**
 *
 *@author chippison
 * Date created: Apr 26, 2017
 **/
/**
 *@ORM\Entity(repositoryClass="AppBundle\Entity\SurveyRepository")
 *@ORM\Table(name="stp_surveys")
 *
 **/
 class Surveys{
 	/** @ORM\Column(type="integer", name="id") @ORM\Id @ORM\GeneratedValue*/
 	private $id;
 	/** @ORM\Column(type="integer", name="ms_id") */
 	private $ms_id;
 	/** @ORM\Column(type="integer", name="moe_id") */
 	private $moe_id;
 	/** @ORM\Column(type="integer", name="cal_year") */
 	private $cal_year;
 	/** @ORM\Column(type="integer", name="test_id") */
 	private $test_id;
 	/** @ORM\Column(type="integer", name="col_reporting") */
 	private $cols;
	public function getId() {
		return $this->id;
	}
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	public function getMsId() {
		return $this->ms_id;
	}
	public function setMsId($ms_id) {
		$this->ms_id = $ms_id;
		return $this;
	}
	public function getMoeId() {
		return $this->moe_id;
	}
	public function setMoeId($moe_id) {
		$this->moe_id = $moe_id;
		return $this;
	}
	public function getCalYear() {
		return $this->cal_year;
	}
	public function setCalYear($cal_year) {
		$this->cal_year = $cal_year;
		return $this;
	}
	public function getTestId() {
		return $this->test_id;
	}
	public function setTestId($test_id) {
		$this->test_id = $test_id;
		return $this;
	}
	public function getCols() {
		return $this->cols;
	}
	public function setCols($cols) {
		$this->cols = $cols;
		return $this;
	}


 }

 class SurveyRepository extends EntityRepository{
 	public function getSurveys($schl_id){
 		$dql = 'SELECT s.ms_id,s.moe_id,s.test_id,s.cal_year FROM AppBundle\Entity\Surveys s
 				WHERE s.moe_id = :id';
 		$q = $this->_em->createQuery($dql);
 		$q->setParameter('id', $schl_id);
 		$res = $q->getResult();
 		return $res;
 	}
 }