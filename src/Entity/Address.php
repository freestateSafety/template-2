<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Address
 *
 * @ORM\Table(name="address")
 * @ORM\Entity(repositoryClass="App\Repository\AddressRepository")
 */
class Address implements \Stringable
{
    final public const TYPE_BILLING = 'billing';

    final public const TYPE_SHIPPING = 'shipping';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="addresses")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private ?\App\Entity\Customer $customer = null;

    /**
     * @ORM\Column(name="address_type", type="string", columnDefinition="ENUM('billing','shipping') NOT NULL")
     * @Assert\NotBlank()
     */
    private string $type;

    /**
     * @ORM\Column(name="address1", type="string", length=255)
     * @Assert\NotBlank()
     */
    private string $addressLine1;

    /**
     * @ORM\Column(name="address2", type="string", length=255, nullable=true)
     */
    private ?string $addressLine2 = null;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     * @Assert\NotBlank()
     */
    private string $city;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=2)
     * @Assert\NotBlank()
     */
    private string $state;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=5)
     * @Assert\NotBlank()
     */
    private string $zip;
    
    public function __construct(string $type = null)
    {
        if (!empty($type)) {
            $this->type = $type;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $address = $this->addressLine1.PHP_EOL;
        if (!empty($this->addressLine2)) {
            $address .= $this->addressLine2.PHP_EOL;
        }
        $address .= $this->city.' '.$this->state.', '.$this->zip;
        return $address;
    }

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress1(): string
    {
        return $this->addressLine1;
    }

    /**
     * @return ?string
     */
    public function getAddress2(): ?string
    {
        return $this->addressLine2;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Get id
     *
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->id?? null;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param \BigE\SimpleUps\Entity\Address $class
     * @return \BigE\SimpleUps\Entity\Address
     */
    public function getUpsAddress(\BigE\SimpleUps\Entity\Address $class): \BigE\SimpleUps\Entity\Address
    {
        return new $class(
            [
                $this->getAddress1(),
                $this->getAddress2(),
            ],
            $this->getCity(),
            'US',
            $this->getState(),
            $this->getZip()
        );
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * Set addressLine1
     *
     * @param string $addressLine1
     * @return Address
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    /**
     * Get addressLine1
     *
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * Set addressLine2
     *
     * @param string $addressLine2
     * @return Address
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    /**
     * Get addressLine2
     *
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Address
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return Address
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }
}
