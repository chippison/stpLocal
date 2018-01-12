<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 *
 *@author chippison
 * Date created: Apr 27, 2017
 **/
/**
 *@ORM\Entity
 *@ORM\Table(name="stp_survey_users")
 *
 **/
 class SurveyUser{
 	/** @ORM\Column(type="integer", name="id") @ORM\Id @ORM\GeneratedValue*/
 	private $id;
 	/** @ORM\Column(type="integer", name="ms_id") */
 	private $ms_id;
 	/** @ORM\Column(type="string", length=100, name="email") */
 	private $email;
 	/** @ORM\Column(type="string", length=50, name="token") */
 	private $token;
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
	public function getEmail() {
		return $this->email;
	}
	public function setEmail($email) {
		$this->email = $email;
		return $this;
	}
	public function getToken() {
		return $this->token;
	}
	public function setToken($token) {
		$this->token = $token;
		return $this;
	}



 }