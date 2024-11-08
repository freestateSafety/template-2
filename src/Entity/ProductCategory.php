<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProductCategory
 *
 * @ORM\Table(name="product_category", uniqueConstraints={@ORM\UniqueConstraint(name="company_priority", columns={"parent_id", "priority"})})
 * @ORM\Entity(repositoryClass="App\Repository\ProductCategoryRepository")
 */
class ProductCategory implements \JsonSerializable, \Stringable
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
     * @ORM\ManyToOne(targetEntity="App\Entity\ProductCategory", inversedBy="subCategories", fetch="EAGER")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    private ?\App\Entity\ProductCategory $parent = null;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $label;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="productCategory")
     */
    private $products;

    /**
     * @var ?int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $priority;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Image()
     */
    private $image;

    /**
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ProductCategory", mappedBy="parent", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"priority" = "ASC"})
     */
    private \Doctrine\ORM\PersistentCollection $subCategories;

    public function __toString(): string
    {
        return $this->label;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function addSubCategory(ProductCategory $category)
    {
        if (is_null($this->parent)) {
            $this->subCategories->add($category);

            return $this;
        }

        throw new \Exception('Only primary categories can have sub categories');
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
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return ProductCategory|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    public function getSlug()
    {
        // replace all special chars with a dash
        $find = [' ', '&', '\r\n', '\n', '+', ','];
        $label = str_replace($find, '-', strtolower($this->getLabel()));

        // strip all other characters
        $find = ['/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/'];
        $replace = ['', '-', ''];
        $label = preg_replace ($find, $replace, $label);

        // return the clean slug
        return $label;
    }

    /**
     * @return ArrayCollection
     */
    public function getSubCategories()
    {
        return $this->subCategories;
    }

    /**
     * @return bool
     */
    public function removeSubCategory(ProductCategory $category)
    {
        return $this->subCategories->removeElement($category);
    }

    /**
     * @param string $image
     * @return $this
     */
    public function setImage($image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @param $label
     * @return $this
     */
    public function setLabel($label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return $this
     */
    public function setParent(ProductCategory $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @param ?int $priority
     *
     * @return $this
     */
    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize(): mixed
    {
        return ['id' => $this->id, 'label' => $this->label, 'parent' => $this->parent, 'priority' => $this->priority];
    }
}
