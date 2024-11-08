<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PasswordToken
 *
 * @ORM\Table(name="password_token")
 * @ORM\Entity(repositoryClass="App\Repository\PasswordTokenRepository")
 */
class PasswordToken
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=64, unique=true)
     */
    private $token;

    /**
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Customer", inversedBy="passwordToken")
     * @ORM\JoinColumn(name="customer_id", unique=true)
     */
    private ?\App\Entity\Customer $customer = null;

    /**
     * @ORM\Column(name="expires", type="datetime")
     */
    private \DateTime $expires;

    public function __construct()
    {
        $this->expires = new \DateTime('+1 day');
    }

    public static function generate(Customer $customer)
    {
        $obj = new static();
        $obj->setCustomer($customer);
        $token = sha1(sha1($customer->getEmail()).sha1($obj->getExpires()->format('r')));
        $obj->setToken($token);
        return $obj;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

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
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return $this
     */
    public function setExpires(\DateTime $dateTime)
    {
        $this->expires = $dateTime;

        return $this;
    }

    /**
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }
}
