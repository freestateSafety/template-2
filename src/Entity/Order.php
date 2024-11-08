<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Order
 *
 * @ORM\Table(name="order_record")
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\EntityListeners({"App\Listener\Entity\OrderListener"})
 */
class Order
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="orders", cascade={"all"})
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private ?Customer $customer = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Payment", mappedBy="order", cascade={"all"}, orphanRemoval=true)
     */
    private ?Payment $payment = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\OrderShipment", mappedBy="order", cascade={"all"}, orphanRemoval=true)
     */
    private ?OrderShipment $shipment = null;

    /**
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private ?string $notes = null;

    /**
     * @ORM\Column(name="fulfilled", type="boolean", options={"default": false})
     */
    private bool $fulfilled = false;

    /**
     * @ORM\Column(name="deleted", type="boolean", options={"default": false})
     */
    private bool $deleted = false;

    /**
     * @ORM\Column(name="created", type="datetime")
     */
    private \DateTime $created;

    /**
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private ?\DateTime $updated = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderProduct", mappedBy="order", cascade={"all"}, orphanRemoval=true)
     */
    private \Doctrine\Common\Collections\Collection|array $products;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->created = new \DateTime();
        $this->products = new ArrayCollection();
    }

    /**
     * Add products
     *
     * @param integer $quantity
     * @return Order
     */
    public function addProduct(Product $product, $quantity = null)
    {
        $orderProduct = new OrderProduct();
        $orderProduct->setOrder($this);
        if (!is_null($quantity)) {
            $orderProduct->setQuantity($quantity);
        }
        $orderProduct->setProduct($product);
        $this->products[] = $orderProduct;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @return bool
     */
    public function getFulfilled()
    {
        return $this->fulfilled;
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
     * @return null|string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    public function getNumber()
    {
        return $this->getId().'-'.$this->created->setTimezone(new \DateTimeZone('UTC'))->format('ymdHi');
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return OrderShipment
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    public function getTotal()
    {
        $total = 0.00;

        foreach ($this->products as $product) {
            $total += $product->getTotal();
        }

        return $total;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        $weight = 0.00;

        /** @var Product $product */
        foreach ($this->products as $product) {
            $weight += $product->getWeight();
        }

        return $weight;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    public function isDeleted()
    {
        return $this->deleted;
    }

    public function isFulfilled()
    {
        return $this->fulfilled;
    }

    /**
     * Remove products
     */
    public function removeProduct(Product $product)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('product', $product));
        $orderProduct = $this->products->matching($criteria)->first();
        $this->products->removeElement($orderProduct);
    }

    /**
     * @return Order
     */
    public function setCreated(\DateTime $dateTime)
    {
        $this->created = $dateTime;

        return $this;
    }

    /**
     * @return Order
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    public function setDeleted($deleted)
    {
        $this->deleted = (bool)$deleted;

        return $this;
    }

    /**
     * @param $fulfilled
     * @return $this
     */
    public function setFulfilled($fulfilled)
    {
        $this->fulfilled = (bool)$fulfilled;

        return $this;
    }

    /**
     * @param null|string $notes
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Order
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @param OrderShipment $shipment
     * @return Order
     */
    public function setShipment($shipment)
    {
        $this->shipment = $shipment;

        return $this;
    }

    /**
     * @return Order
     */
    public function setUpdated(\DateTime $dateTime)
    {
        $this->updated = $dateTime;

        return $this;
    }
}
