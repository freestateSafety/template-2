<?php

namespace App\Entity;

use App\Model\CreditCard;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Doctrine\ORM\Mapping as ORM;

/**
 * Payment
 *
 * @ORM\Table(name="payment")
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment implements \Serializable
{
    final public const METHOD_CARD = 'card';

    final public const METHOD_INVOICE = 'invoice';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Order", inversedBy="payment")
     */
    private ?\App\Entity\Order $order = null;

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", columnDefinition="ENUM('card', 'invoice') NOT NULL")
     */
    private $method;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="text", nullable=true)
     */
    private $details;

    /**
     * @ORM\Column(name="total", type="float", precision=10, scale=2)
     */
    private ?float $total = null;

    public function __serialize()
    {
        return [$this->id, $this->method, $this->details];
    }

    public function __unserialize(array $data)
    {
        [$this->id, $this->method, $this->details] = $data;
    }

    /**
     * @param Key $key
     * @return CreditCard
     */
    public function getCreditCard($key)
    {
        return unserialize($this->getDetails($key));
    }

    /**
     * @param Key $key If false, raw string is returned
     * @return string
     */
    public function getDetails($key)
    {
        // Return raw details if key is false
        if ($key === false || empty($this->details)) {
            return $this->details;
        }

        return Crypto::decrypt($this->details, $key);
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
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
     * @param string $details
     * @param Key $key
     * @return $this
     */
    public function setDetails(string $details, Key $key): self
    {
        $details = Crypto::encrypt($details, $key);
        $this->details = $details;

        return $this;
    }

    /**
     * @param string $method
     * @return Payment
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return Payment
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @param float $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = (float)$total;

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
