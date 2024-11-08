<?php
namespace App\Twig\Extension;

use App\Service\CartService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CartExtension extends AbstractExtension
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [new TwigFunction('cart_size', fn() => sizeof($this->cartService->getItems()))];
    }

    public function getName(): string
    {
        return 'app.twig.extension.cart';
    }
}
