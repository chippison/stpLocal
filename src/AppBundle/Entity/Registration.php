<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 *
 *@author chippison
 * Date created: 29 Nov 2017
 **/
/**
 *@ORM\Entity
 *@ORM\Table(name="stp_registration")
 *
 **/
class Registration
{
    /** @ORM\Column(type="integer", name="id") @ORM\Id @ORM\GeneratedValue*/
    private $id;
    /** @ORM\Column(type="integer", name="calYr") */
    private $calYr;
    /** @ORM\Column(type="datetime", name="lastModified") **/
    private $lastModified;
    /** @ORM\Column(type="integer", name="moe") */
    private $moe;
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCalYr()
    {
        return $this->calYr;
    }

    /**
     * @return mixed
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @return mixed
     */
    public function getMoe()
    {
        return $this->moe;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $calYr
     */
    public function setCalYr($calYr)
    {
        $this->calYr = $calYr;
    }

    /**
     * @param mixed $lastModified
     */
    public function setLastModified($s='now')
    {
        $this->lastModified = new \DateTime($s);
    }

    /**
     * @param mixed $moe
     */
    public function setMoe($moe)
    {
        $this->moe = $moe;
    }


}

