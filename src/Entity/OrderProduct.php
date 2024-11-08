<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderProduct
 *
 * @ORM\Table(name="order_product")})
 * @ORM\Entity(repositoryClass="App\Repository\OrderProductRepository")
 */
class OrderProduct
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="products", cascade={"all"})
     */
    private ?\App\Entity\Order $order = null;

    /**
     * This is the original product record for reference, the product info will
     * be copied here for order integrity.
     *
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private ?\App\Entity\Product $product = null;

    /**
     * @ORM\Column(name="quantity", type="integer", options={"default": 1})
     */
    private int $quantity = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="itemNumber", type="string", length=50)
     */
    private $itemNumber;

    /**
     * @var float
     *
     * @ORM\Column(name="weight", type="decimal", precision=10, scale=2)
     */
    private $weight;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getItemNumber()
    {
        return $this->itemNumber;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->price * $this->quantity;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param $itemNumber
     * @return OrderProduct
     */
    public function setItemNumber($itemNumber)
    {
        $this->itemNumber = $itemNumber;

        return $this;
    }

    /**
     * @param $name
     * @return OrderProduct
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return OrderProduct
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @param $price
     * @return OrderProduct
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return OrderProduct
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
        $this->setName($product->getName());
        $this->setItemNumber($product->getItemNumber());
        $this->setPrice($product->getPrice($this->getQuantity()));
        $this->setWeight($product->getWeight());

        return $this;
    }

    /**
     * @param $quantity
     * @return OrderProduct
     */
    public function setQuantity($quantity)
    {
        $this->quantity = intval($quantity);

        return $this;
    }

    /**
     * @param $weight
     * @return OrderProduct
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }
}
