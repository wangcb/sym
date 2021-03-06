<?php
namespace Acme\DemoBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\Table(name="user")
 * @ORM\HasLifecycleCallbacks();
 */
class User{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	/**
	 * @ORM\Column(type="string")
	 */
	protected $username;
	/**
	 * @ORM\Column(type="string")
	 */
	protected $email;
	/**
	 * @ORM\Column(type="string")
	 */
	protected $password;
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $sex;
	/**
	 * @ORM\Column(type="string")
	 */
	protected $mobile;
	/**
	 * @ORM\Column(type="string")
	 */
	protected $qq;
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $addtime;
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $uptime;
	

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set sex
     *
     * @param integer $sex
     * @return User
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex
     *
     * @return integer 
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return User
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string 
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set qq
     *
     * @param string $qq
     * @return User
     */
    public function setQq($qq)
    {
        $this->qq = $qq;

        return $this;
    }

    /**
     * Get qq
     *
     * @return string 
     */
    public function getQq()
    {
        return $this->qq;
    }

    /**
     * Set addtime
     *
     * @param integer $addtime
     * @return User
     */
    public function setAddtime($addtime)
    {
        $this->addtime = $addtime;

        return $this;
    }

    /**
     * Get addtime
     *
     * @return integer 
     */
    public function getAddtime()
    {
        return $this->addtime;
    }

    /**
     * Set uptime
     *
     * @param integer $uptime
     * @return User
     */
    public function setUptime($uptime)
    {
        $this->uptime = $uptime;

        return $this;
    }

    /**
     * Get uptime
     *
     * @return integer 
     */
    public function getUptime()
    {
        return $this->uptime;
    }
    
    /**
     * @ORM\PrePersist()
     */
    public function PrePersist(){
    	if ($this->getAddtime() == null) {
    		$this->setAddtime(time());
    	}
    	$this->setUptime(time());
    }
    
    /**
     * @ORM\PreUpdate()
     */
    public function PreUpdate(){
    	$this->setUptime(time());
    }
}
