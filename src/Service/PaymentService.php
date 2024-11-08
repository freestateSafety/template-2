<?php
namespace App\Service;

use App\Entity\Customer;
use App\Model\CreditCard;
use Symfony\Component\HttpFoundation\Request;

abstract class PaymentService
{
    private readonly string $template;

    public function __construct($template)
    {
        $this->template = (string)$template;
    }

    /**
     * @return array
     */
    public function getRenderArgs(): array
    {
        return [];
    }

    /**
     * @return string
     */
    final public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return false|CreditCard
     */
    public abstract function handleRequest(Request $request, Customer $customer): bool|CreditCard;
}
