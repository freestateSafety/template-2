<?php
namespace App\Service\Payment;

use App\Entity\Customer;
use App\Model\CreditCard;
use App\Service\PaymentService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class GenericService extends PaymentService
{
    /**
     * @var FormInterface
     */
    private readonly FormInterface $creditCardForm;

    public function __construct(private readonly FormFactoryInterface $formFactory)
    {
        parent::__construct('cart/payment.html.twig');

        $this->creditCardForm = $this->formFactory->create(\App\Form\Type\CreditCardType::class);
    }

    public function getRenderArgs(): array
    {
        return ['paymentForm' => $this->creditCardForm->createView()];
    }

    public function handleRequest(Request $request, Customer $customer): bool|CreditCard
    {
        $this->creditCardForm->handleRequest($request);

        if ($this->creditCardForm->isSubmitted() && $this->creditCardForm->isValid()) {
            return $this->creditCardForm->getData();
        }

        return false;
    }
}
