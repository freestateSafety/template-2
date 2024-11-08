<?php
namespace App\Service;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class CartService
 * @package App
 */
class CartService
{
    /**
     * @var Product[]
     */
    private array $items = [];

    /**
     * @var ManagerRegistry
     */
    private $productRepository;

    /**
     * @var int[]
     */
    private array $quantity = [];

    private ?SessionInterface $session = null;

    private ?float $shipping = null;

    /**
     * @var string
     */
    private $shippingMethod;

    private ?\App\Entity\Address $shippingAddress = null;

    /**
     * @var string
     */
    private $shippingAccount;

    public function __construct(private readonly RequestStack $requestStack, ManagerRegistry $registry)
    {
        if (($request = $requestStack->getCurrentRequest()) && $request->hasSession()) {
            $this->session = $requestStack->getSession();
        }

        $this->productRepository = $registry->getRepository(\App\Entity\Product::class);
        if ($this->session && $this->session->has('gclabel.cart')) {
            foreach ($this->session->get('gclabel.cart') as $array) {
                $product = $this->productRepository->find($array[0]);
                // TODO: could do a bit better error checking..
                if (!empty($product)) {
                    $this->items[$product->getId()] = $product;
                    $this->quantity[$product->getId()] = $array[1];
                }
            }

            $this->setShipping($this->session->get('gclabel.cart.shipping', null));
            if ($this->session->has('gclabel.cart.shippingAddress')) {
                $this->setShippingAddress($this->session->get('gclabel.cart.shippingAddress'));
            }
            $this->setShippingMethod($this->session->get('gclabel.cart.shippingMethod', null));
            $this->setShippingAccount($this->session->get('gclabel.cart.shippingAccount', null));
        }
    }

    /**
     * @param int $quantity
     * @return CartService
     */
    public function add(Product $product, $quantity = 1)
    {
        if (!isset($this->items[$product->getId()])) {
            $this->items[$product->getId()] = $product;
            $this->quantity[$product->getId()] = 0;
        }

        // we're adding to the quantity, not updating it
        $this->quantity[$product->getId()] += $quantity;
        return $this;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->items = [];
        $this->quantity = [];
        $this->shipping = null;
        $this->shippingMethod = null;

        $this->session->remove('gclabel.cart');
        $this->session->remove('gclabel.cart.shipping');
        $this->session->remove('gclabel.cart.shippingMethod');
        $this->session->remove('gclabel.cart.shippingAddress');
        $this->session->remove('gclabel.cart.shippingAccount');

        return $this;
    }

    /**
     * @return Product[]
     */
    public function getItems()
    {
        return $this->items;
    }

    public function getQuantity($productId = null)
    {
        if (empty($productId)) {
            return array_sum($this->quantity);
        } else {
            return $this->quantity[$productId];
        }
    }

    /**
     * @return float
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @return string
     */
    public function getShippingAccount()
    {
        return $this->shippingAccount;
    }

    /**
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }

    /**
     * @param bool $shipping
     * @return float
     */
    public function getTotal($shipping = true)
    {
        $total = array_sum(array_map(function (Product $product) {
            $quantity = $this->getQuantity($product->getId());
            return $product->getPrice($quantity) * $quantity;
        }, $this->items));

        if ($shipping === true && $this->shipping > 0) {
            $total += $this->shipping;
        }

        return $total;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return array_sum(array_map(fn(Product $product) => $product->getWeight() * $this->getQuantity($product->getId()), $this->items));
    }

    /**
     * @return int
     */
    public function length()
    {
        return sizeof($this->items);
    }

    /**
     * @return $this
     */
    public function remove(Product $product)
    {
        if (isset($this->items[$product->getId()])) {
            unset($this->items[$product->getId()]);
            unset($this->quantity[$product->getId()]);
        }

        return $this;
    }

    public function save()
    {
        if (empty($this->items)) {
            if ($this->session->has('gclabel.cart')) {
                $this->session->remove('gclabel.cart');
                $this->session->remove('gclabel.cart.shipping');
                $this->session->remove('gclabel.cart.shippingMethod');
                $this->session->remove('gclabel.cart.shippingAddress');
                $this->session->remove('gclabel.cart.shippingAccount');
                return $this->session->save();
            }

            return true;
        }

        $cart = [];

        foreach ($this->items as $item) {
            $cart[] = [$item->getId(), $this->quantity[$item->getId()]];
        }

        $this->session->set('gclabel.cart', $cart);
        $this->session->set('gclabel.cart.shipping', $this->shipping);
        $this->session->set('gclabel.cart.shippingMethod', $this->shippingMethod);
        $this->session->set('gclabel.cart.shippingAddress', $this->shippingAddress);
        $this->session->set('gclabel.cart.shippingAccount', $this->shippingAccount);
        return $this->session->save();
    }

    /**
     * @param float $total
     * @return $this
     */
    public function setShipping($total)
    {
        $this->shipping = (float)$total;

        return $this;
    }

    /**
     * @param string $account
     * @return $this
     */
    public function setShippingAccount($account)
    {
        $this->shippingAccount = $account;

        return $this;
    }

    /**
     * @return $this
     */
    public function setShippingAddress(Address $address=null)
    {
        $this->shippingAddress = $address;

        return $this;
    }

    /**
     * @param string $shippingMethod
     * @return $this
     */
    public function setShippingMethod($shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    /**
     * @param $quantity
     * @return $this
     */
    public function update(Product $product, $quantity)
    {
        if (isset($this->items[$product->getId()])) {
            $this->quantity[$product->getId()] = $quantity;
        }

        return $this;
    }
}
