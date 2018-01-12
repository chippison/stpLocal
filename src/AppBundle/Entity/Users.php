<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 *
 *@author chippison
 * Date created: Apr 21, 2017
 **/
/**
 *@ORM\Entity
 *@ORM\Table(name="stp_users")
 *
 **/
 class Users{
 	/** @ORM\Column(type="integer", name="id") @ORM\Id @ORM\GeneratedValue*/
 	private $id;
 	/** @ORM\Column(type="string", length=255, name="email") */
 	private $email;
 	/** @ORM\Column(type="string", length=255, name="username") */
 	private $username;
 	/** @ORM\Column(type="integer", name="moe_id") */
 	private $moe_id;
 	/** @ORM\Column(type="string", length=20, name="role") */
 	private $roles;
	public function getId() {
		return $this->id;
	}
	public function getEmail() {
		return $this->email;
	}
	public function setEmail($email) {
		$this->email = $email;
		return $this;
	}
	public function getUsername() {
		return $this->username;
	}
	public function setUsername($username) {
		$this->username = $username;
		return $this;
	}
	public function getMoeId() {
		return $this->moe_id;
	}
	public function setMoeId($moe_id) {
		$this->moe_id = $moe_id;
		return $this;
	}
	public function getRoles() {
		return explode('|', $this->roles);
		//return $this->roles;
	}
	public function setRoles($roles) {
		$this->roles = $roles;
		return $this;
	}



}