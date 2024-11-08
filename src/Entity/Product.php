<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="product", indexes={@ORM\Index(columns={"name","notes"}, flags={"fulltext"})})
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product implements \Stringable
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
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ProductCategory", inversedBy="products")
     * @ORM\JoinColumn(name="product_category_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private ?\App\Entity\ProductCategory $productCategory = null;

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=20, nullable=true)
     */
    private $class;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="item_number", type="string", length=50)
     */
    private $itemNumber;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Material", inversedBy="products")
     * @ORM\JoinColumn(name="material_id", referencedColumnName="id", nullable=false)
     */
    private ?\App\Entity\Material $material = null;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=35)
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="shape", type="string", length=25, nullable=true)
     */
    private $shape;

    /**
     * @var string
     *
     * @ORM\Column(name="quantity", type="string", length=25, nullable=true)
     */
    private $quantity;

    /**
     * @ORM\Column(name="weight", type="decimal", precision=10, scale=2, options={"default": 0.00})
     */
    private float $weight = 0.00;

    /**
     * @ORM\Column(name="is_viewable", type="boolean", options={"default": true})
     */
    private bool $viewable = true;

    /**
     * @ORM\Column(name="notes", type="string", length=255, nullable=true)
     */
    private ?string $notes;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\Image()
     */
    private $image;

    /**
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ProductQuantity", mappedBy="product")
     * @ORM\OrderBy({"quantity" = "ASC"})
     */
    private \Doctrine\Common\Collections\Collection|array $quantities;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->quantities = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @param array $fields
     * @return array
     */
    public function toArray($fields = [])
    {
        $product = ['id' => $this->id, 'productCategory' => $this->productCategory->getId(), 'class' => $this->class, 'name' => $this->name, 'itemNumber' => $this->itemNumber, 'material' => $this->material->getId(), 'size' => $this->size, 'shape' => $this->shape, 'quantity' => $this->quantity, 'weight' => $this->weight, 'viewable' => $this->viewable, 'notes' => $this->notes, 'image' => $this->image, 'quantities' => $this->quantities->toArray()];

        return (empty($fields))? $product : array_intersect_key($product, array_flip($fields));
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
     * Set class
     *
     * @param string $class
     * @return Product
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set itemNumber
     *
     * @param string $itemNumber
     * @return Product
     */
    public function setItemNumber($itemNumber)
    {
        $this->itemNumber = $itemNumber;

        return $this;
    }

    /**
     * Get itemNumber
     *
     * @return string
     */
    public function getItemNumber()
    {
        return $this->itemNumber;
    }

    /**
     * Set size
     *
     * @param string $size
     * @return Product
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set shape
     *
     * @param string $shape
     * @return Product
     */
    public function setShape($shape)
    {
        $this->shape = $shape;

        return $this;
    }

    /**
     * Get shape
     *
     * @return string
     */
    public function getShape()
    {
        return $this->shape;
    }

    /**
     * Set quantity
     *
     * @param string $quantity
     * @return Product
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set weight
     *
     * @param float $weight
     * @return Product
     */
    public function setWeight($weight)
    {
        $this->weight = (float)$weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set viewable
     *
     * @param boolean $viewable
     * @return Product
     */
    public function setViewable($viewable)
    {
        $this->viewable = $viewable;

        return $this;
    }

    /**
     * Get viewable
     *
     * @return boolean
     */
    public function getViewable()
    {
        return $this->viewable;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return Product
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set productCategory
     *
     * @return Product
     */
    public function setProductCategory(ProductCategory $productCategory = null)
    {
        $this->productCategory = $productCategory;

        return $this;
    }

    /**
     * Get productCategory
     *
     * @return \App\Entity\ProductCategory
     */
    public function getProductCategory()
    {
        return $this->productCategory;
    }

    /**
     * Add quantities
     *
     * @return Product
     */
    public function addQuantity(ProductQuantity $quantities)
    {
        $this->quantities[] = $quantities;

        return $this;
    }

    /**
     * Remove quantities
     */
    public function removeQuantity(ProductQuantity $quantities)
    {
        $this->quantities->removeElement($quantities);
    }

    /**
     * Get quantities
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getQuantities()
    {
        return $this->quantities;
    }

    /**
     * Set material
     *
     * @return Product
     */
    public function setMaterial(\App\Entity\Material $material)
    {
        $this->material = $material;

        return $this;
    }

    /**
     * Get material
     *
     * @return \App\Entity\Material
     */
    public function getMaterial()
    {
        return $this->material;
    }

    public function getPrice($quantity = 1)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->gte('quantity', $quantity))
            ->orderBy(['quantity' => 'ASC'])
            ->setMaxResults(1);
        $prices = $this->getQuantities()->matching($criteria);

        if (!$prices->count()) {
            $criteria = Criteria::create()
                ->orderBy(['quantity' => 'DESC'])
                ->setMaxResults(1);
            $prices = $this->getQuantities()->matching($criteria);
            if (!$prices->count()) return null;
        };

        return $prices->first()->getPrice();
    }

    /**
     * @return ProductCategory
     */
    public function getCategory()
    {
        return $this->productCategory->getParent();
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }
}
