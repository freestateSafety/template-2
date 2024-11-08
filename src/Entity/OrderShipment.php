<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderShipping
 *
 * @ORM\Table(name="order_shipment")
 * @ORM\Entity(repositoryClass="App\Repository\OrderShipmentRepository")
 */
class OrderShipment implements \Serializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Order", inversedBy="shipment", cascade={"all"})
     */
    private ?\App\Entity\Order $order = null;

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", length=50)
     */
    private string $method;

    /**
     * @var string
     *
     * @ORM\Column(name="address1", type="string", length=255)
     */
    private string $addressLine1;

    /**
     * @var ?string
     *
     * @ORM\Column(name="address2", type="string", length=255, nullable=true)
     */
    private ?string $addressLine2 = null;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     */
    private string $city;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=2)
     */
    private string $state;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=5)
     */
    private string $zip;

    /**
     * @ORM\Column(name="total", type="float", precision=10, scale=2)
     */
    private ?float $total = null;

    /**
     * @var ?string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $shippingAccount = null;

    /**
     * @var ?string
     *
     * @ORM\Column(name="tracking_number", type="string", length=50, nullable=true)
     */
    private ?string $trackingNumber = null;

    /**
     * @return array
     */
    public function __serialize()
    {
        return [$this->addressLine1, $this->addressLine2, $this->city, $this->method, $this->state, $this->total, $this->zip];
    }

    public function __unserialize(array $data)
    {
        [$this->addressLine1, $this->addressLine2, $this->city, $this->method, $this->state, $this->total, $this->zip] = $data;
    }

    /**
     * @return Address|static
     */
    public static function newFromAddress(Address $address)
    {
        $obj = new static();
        $obj->setAddressLine1($address->getAddressLine1());
        $obj->setAddressLine2($address->getAddressLine2());
        $obj->setCity($address->getCity());
        $obj->setState($address->getState());
        $obj->setZip($address->getZip());
        return $obj;
    }

    public function getAddress()
    {
        $address = $this->addressLine1.PHP_EOL;
        if (!empty($this->addressLine2)) {
            $address .= $this->addressLine2.PHP_EOL;
        }
        $address .= $this->city.' '.$this->state.', '.$this->zip;
        return $address;
    }

    /**
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
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
    public function getMethod()
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
     * @return string
     */
    public function getShippingAccount()
    {
        return $this->shippingAccount;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return null|string
     */
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $addressLine1
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;
    }

    /**
     * @param string $addressLine2
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @param string $method
     * @return OrderShipment
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return OrderShipment
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @param string $shippingAccount
     *
     * @return $this
     */
    public function setShippingAccount($shippingAccount)
    {
        $this->shippingAccount = $shippingAccount;

        return $this;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @param $total
     * @return OrderShipment
     */
    public function setTotal($total)
    {
        $this->total = (float)$total;

        return $this;
    }

    /**
     * @param null|string $trackingNumber
     * @return $this
     */
    public function setTrackingNumber($trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return \serialize($this->__serialize());
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
        $this->__unserialize(\unserialize($serialized));
    }
}
