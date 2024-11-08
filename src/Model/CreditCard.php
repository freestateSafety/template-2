<?php
namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CreditCard implements \Serializable
{
    /**
     * @var string
     *
     * @Assert\Type(type="string")
     */
    private $cvv;

    /**
     * @var int
     *
     * @Assert\Type(type="integer")
     */
    private $expireMonth;

    /**
     * @var int
     *
     * @Assert\Type(type="integer")
     */
    private $expireYear;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\CardScheme(schemes={"AMEX", "MASTERCARD", "VISA"}, message="Credit card number is invalid")
     */
    private $number;

    /**
     * @return array
     */
    public function __serialize() : array
    {
        return [$this->cvv, $this->expireMonth, $this->expireYear, $this->name, $this->number];
    }

    /**
     * @return void
     */
    public function __unserialize(array $data)
    {
        [$this->cvv, $this->expireMonth, $this->expireYear, $this->name, $this->number, ] = $data;
    }

    /**
     * @return string
     */
    public function getCvv()
    {
        return $this->cvv;
    }

    /**
     * @return int
     */
    public function getExpireMonth()
    {
        return $this->expireMonth;
    }

    /**
     * @return int
     */
    public function getExpireYear()
    {
        return $this->expireYear;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getNumberMasked()
    {
        return str_repeat('X', strlen($this->number) - 4).substr($this->number, -4);
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize($this->__serialize());
    }

    /**
     * @param $cvv
     * @return CreditCard
     */
    public function setCvv($cvv)
    {
        $this->cvv = $cvv;

        return $this;
    }

    /**
     * @param $expireMonth
     * @return CreditCard
     */
    public function setExpireMonth($expireMonth)
    {
        $this->expireMonth = $expireMonth;

        return $this;
    }

    /**
     * @param $expireYear
     * @return CreditCard
     */
    public function setExpireYear($expireYear)
    {
        $this->expireYear = $expireYear;

        return $this;
    }

    /**
     * @param $name
     * @return CreditCard
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param $number
     * @return CreditCard
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $this->__unserialize(unserialize($serialized));
    }
}