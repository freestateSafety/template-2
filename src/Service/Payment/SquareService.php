<?php
namespace App\Service\Payment;

use App\Entity\Customer;
use App\Model\CreditCard\Square;
use App\PaymentService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

class SquareService extends PaymentService
{
    public function __construct(ContainerInterface $container, EntityManager $em, FormFactory $formFactory)
    {
        parent::__construct($container, $em, $formFactory, ':cart/payment:square.html.twig');
    }

    public function handleRequest(Request $request, Customer $customer)
    {
        if ($request->getMethod() == Request::METHOD_POST) {
            $creditCard = new Square();
            $creditCard->setNonce($request->request->get('square_nonce'));
            $tmp = json_decode($request->request->get('square_data'), null, 512, JSON_THROW_ON_ERROR);
            $creditCard->setExpireMonth($tmp->exp_month);
            $creditCard->setExpireYear($tmp->exp_year);
            $creditCard->setNumber($tmp->last_4);
            $creditCard->setName($customer->getName());
            return $creditCard;
        }

        return false;
    }
}