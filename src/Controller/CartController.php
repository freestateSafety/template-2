<?php
namespace App\Controller;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderShipment;
use App\Entity\Payment;
use App\Service\CartService;
use App\Service\Payment\GenericService;
use App\Service\UPSService;
use BigE\SimpleUps\Entity\Rate\RateResponse\RatedShipment;
use BigE\SimpleUps\Entity\ServiceEnum;
use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class CartController extends AbstractController
{
    public function __construct(private readonly CartService $cart, private readonly UPSService $ups, ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry);
    }

    #[Route('/cart/add', name: 'cart_add', methods: ['POST'])]
    public function addAction(Request $request): RedirectResponse
    {
        $product = $this->managerRegistry->getRepository(\App\Entity\Product::class)->find($request->get('product_id'));
        $this->cart->add($product, $request->get('quantity', 1))->save();
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/confirm', name: 'cart_confirm')]
    public function confirmAction(Request $request): Response
    {
        $session = $request->getSession();
        $order = $session->get('order');
        $session->remove('order');

        if (!$order || ($order = $this->managerRegistry->getRepository(Order::class)->find($order)) === null) {
            return $this->redirectToRoute('homepage');
        }

        $key = Key::loadFromAsciiSafeString($this->getParameter('secret.key'));

        return $this->render('cart/confirm.html.twig', ['creditCard' => (($order->getPayment()->getMethod() === Payment::METHOD_CARD)? $order->getPayment()->getCreditCard($key) : null), 'customer' => $this->getUser(), 'order' => $order]);
    }

     #[Route('/cart', name: 'cart')]
    public function indexAction(Request $request): Response
    {
        if ($this->cart->getQuantity() === 0) {
            return $this->redirectToRoute('homepage');
        }

        return $this->render('cart/index.html.twig', ['cart' => $this->cart]);
    }

    #[Route('/cart/payment', name: 'cart_payment')]
    public function paymentAction(Request $request, GenericService $paymentService): Response
    {
        // We need a shipping method before we can take a payment.
        if (!$this->cart->getShippingMethod()) {
            return $this->redirectToRoute('cart_shipping');
        }

        $paymentOption = $request->get('payment_option');
        $payment = new Payment();

        if ($paymentOption === Payment::METHOD_CARD) {
            if (($creditCard = $paymentService->handleRequest($request, $this->getUser())) !== false) {
                $payment->setMethod(Payment::METHOD_CARD);
                $key = Key::loadFromAsciiSafeString($this->getParameter('secret.key'));
                $payment->setDetails(serialize($creditCard), $key);
                $request->getSession()->set('payment', serialize($payment));
                return $this->redirectToRoute('cart_review');
            }
        } elseif ($paymentOption === Payment::METHOD_INVOICE) {
            $payment->setMethod(Payment::METHOD_INVOICE);
            $request->getSession()->set('payment', serialize($payment));
            return $this->redirectToRoute('cart_review');
        }

        return $this->render($paymentService->getTemplate(), array_merge(['address' => $this->cart->getShippingAddress()], $paymentService->getRenderArgs()));
    }

    #[
        Route('/register', name: 'customer_register'),
        Route('/cart/register', name: 'cart_register')
    ]
    public function registerAction(Request $request, AuthorizationCheckerInterface $authorizationChecker, AuthenticationUtils $authenticationUtils, PasswordHasherFactoryInterface $encoder, TokenStorageInterface $tokenStorage): Response
    {
        if ($authorizationChecker->isGranted('ROLE_USER')) {
            if ($request->get('_route') == 'cart_register') {
                return $this->redirectToRoute('cart_shipping');
            } else {
                return $this->redirectToRoute('user_dashboard');
            }
        }

        // setup the entities
        $customer = new Customer();
        $billingAddress = new Address();
        $billingAddress->setType(Address::TYPE_BILLING);
        $shippingAddress = new Address();
        $shippingAddress->setType(Address::TYPE_SHIPPING);
        // setup the forms
        $billingAddressForm = $this->createForm(\App\Form\Type\AddressBillingType::class, $billingAddress);
        $customerForm = $this->createForm(\App\Form\Type\RegisterType::class, $customer);
        $shippingAddressForm = $this->createForm(\App\Form\Type\AddressShippingType::class, $shippingAddress);
        $customerForm->handleRequest($request);
        $shippingAddressForm->handleRequest($request);
        $billingAddressForm->handleRequest($request);

        if (
            $customerForm->isSubmitted() && $customerForm->isValid()
            &&
            $shippingAddressForm->isSubmitted() && $shippingAddressForm->isValid()
            &&
            $billingAddressForm->isSubmitted() && $billingAddressForm->isValid()
        ) {
            $password = $encoder->getPasswordHasher($customer)->hash($customer->getPlainPassword());
            $customer->setPassword($password);
            $customer->setKey(KeyProtectedByPassword::createRandomPasswordProtectedKey($password));
            $billingAddress->setCustomer($customer);
            $shippingAddress->setCustomer($customer);
            $em = $this->managerRegistry->getManager();
            $em->persist($customer);
            $em->persist($billingAddress);
            $em->persist($shippingAddress);
            $em->flush();
            $token = new UsernamePasswordToken($customer, 'main', $customer->getRoles());
            $tokenStorage->setToken($token);
            $session = $request->getSession();
            $session->set('_security_main', serialize($token));
            $session->set('key', $customer->getKey()->unlockKey($password)->saveToAsciiSafeString());
            if ($request->get('_route') === 'cart_register') {
                return $this->redirectToRoute('cart_shipping');
            } else {
                return $this->redirectToRoute('user_dashboard');
            }
        }

        return $this->render('cart/register.html.twig', ['billingAddressForm' => $billingAddressForm->createView(), 'cart' => $this->cart, 'customerForm' => $customerForm->createView(), 'error' => $authenticationUtils->getLastAuthenticationError(), 'shippingAddressForm' => $shippingAddressForm->createView()]);
    }

    #[Route('/cart/remove', name: 'cart_remove', methods: ['POST'])]
    public function removeAction(Request $request): RedirectResponse
    {
        $product = $this->managerRegistry->getRepository(\App\Entity\Product::class)->find($request->get('product_id'));
        $this->cart->remove($product)->save();
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/review', name: 'cart_review')]
    public function reviewAction(Request $request, MailerInterface $mailer): Response
    {
        $payment = \unserialize($request->getSession()->get('payment'));

        if ($request->isMethod(Request::METHOD_POST)) {
            $order = new Order();
            $order->setCustomer($this->getUser());
            $shipment = OrderShipment::newFromAddress($this->cart->getShippingAddress());
            $shipment->setTotal($this->cart->getShipping());
            $shipment->setMethod($this->cart->getShippingMethod());
            $shipment->setShippingAccount($this->cart->getShippingAccount());
            $shipment->setOrder($order);
            $payment->setOrder($order);
            $payment->setTotal($this->cart->getTotal());
            $em = $this->managerRegistry->getManagerForClass(Order::class);
            $em->persist($order);
            $em->flush();
            foreach ($this->cart->getItems() as $product) {
                $order->addProduct($product, $this->cart->getQuantity($product->getId()));
            }
            $em->persist($order);
            $em->persist($payment);
            $em->persist($shipment);
            $em->flush();
            $this->cart->clear();
            $order->setPayment($payment)->setShipment($shipment);
            $this->addFlash('success', 'Thank you, your order has been placed!');
            $request->getSession()->set('order', $order->getId());
            $email = $this->render('email/order.html.twig', ['order' => $order]);
            
            /** Send order confirmation to customer */
            $confirmMsg = (new Email())
                ->subject('Order Confirmation: ' . $order->getNumber())
                ->html($email->getContent(), 'text/html')
                ->from(new \Symfony\Component\Mime\Address($this->getParameter('smtp_email'), $this->getParameter('site.name')))
                ->replyTo(new \Symfony\Component\Mime\Address($this->getParameter('order_email'), $this->getParameter('site.name')))
                ->to(new \Symfony\Component\Mime\Address($this->getUser()->getEmail(), $this->getUser()->getName()))
                ->addBcc($this->getParameter('order_email'));
            $mailer->send($confirmMsg);
            return $this->redirectToRoute('cart_confirm');
        }

        $key = Key::loadFromAsciiSafeString($this->getParameter('secret.key'));

        return $this->render('cart/review.html.twig', ['cart' => $this->cart, 'creditCard' => $payment->getCreditCard($key), 'customer' => $this->getUser(), 'payment' => $payment]);
    }

    #[Route('/cart/shipping', name: 'cart_shipping')]
    public function shippingAction(Request $request): Response
    {
        $error = null;
        $shippingAddress = $this->getUser()->getAddressesShipping();

        // Create the form used for ajax requests to make new shipping addresses
        $blankAddress = new Address();
        $blankAddress->setType(Address::TYPE_SHIPPING);
        $shippingAddressType = $this->createForm(\App\Form\Type\AddressShippingType::class, $blankAddress);

        if ($request->query->has('shippingAddress') || $request->request->has('shippingAddress')) {
            $address = ($request->query->has('shippingAddress')) ?
                $request->query->getInt('shippingAddress') : $request->request->getInt('shippingAddress');
            $address = $shippingAddress->matching(
                Criteria::create()->where(Criteria::expr()->eq('id', $address))
            )->first();
        } else {
            $address = $shippingAddress->first();
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            if ($request->isXmlHttpRequest()) {
                $shippingAddressType->handleRequest($request);
                if ($shippingAddressType->isSubmitted() && $shippingAddressType->isValid()) {
                    /** @var Address $address */
                    $address = $shippingAddressType->getData();
                    $address->setCustomer($this->getUser());
                    $em = $this->managerRegistry->getManager();
                    $em->persist($address);
                    $em->flush();

                    $rates = $this->ups->getRates($address, $this->cart->getWeight());
                    $html = $this->renderView('fragments/ups-rate-list.html.twig', ['rates' => $rates]);
                    return JsonResponse::fromJsonString(json_encode(['address' => ['id' => $address->getId(), 'text' => (string)$address]]));
                }

                $global = [];
                foreach ($shippingAddressType->getErrors() as $error) {
                    $global[] = $error->getMessage();
                }
                
                $fields = [];
                foreach ($shippingAddressType as $child) {
                    if (!$child->isValid()) {
                        $fields[$child->getName()] = [];
                        foreach ($child->getErrors() as $error) {
                            $fields[$child->getName()][] = $error->getMessage();
                        }
                    }
                }
                
                return JsonResponse::create(['error' => ['global' => $global, 'fields' => $fields]]);
            }

            $shipType = $request->get('shipType');
            if (empty($shipType)) {
                $error = 'You must select a shipment type';
            } elseif ($shipType === 'custom' && !preg_match('#[\w\d]{2,255}#', (string) $request->get('shippingAcctNum'))) {
                $error = 'You must specify a valid shipping account number for custom shipping';
            } else {
                $this->cart->setShippingAddress($address);
                if ($shipType === 'custom') {
                    $this->cart->setShippingMethod(str_replace('_', ' ', (string) $request->get('customShipper')));
                    $this->cart->setShippingAccount($request->get('shippingAcctNum'));
                } else {
                    $this->cart->setShippingMethod(ServiceEnum::from($shipType)->getName());
                    $this->cart->setShipping($request->get('shipCost'));
                }

                $this->cart->save();
                return $this->redirectToRoute('cart_payment');
            }
        }

        $rates = null;
        try {
            $rates = $this->ups->getRates($address, $this->cart->getWeight());
        } catch (\Exception $e) {
            if ($e->getCode() === 111035) {
                $phone = $this->getParameter('site.phone');
                $this->addFlash('error', 'Unable to provide shipping over 150 lbs. Please call ' . $phone . ' for pricing.');
            }

            $this->addFlash('error', 'Unable to retrieve shipping information from UPS: ' . $e->getMessage() );
            return $this->redirectToRoute('cart');
        }

        usort($rates, fn (RatedShipment $a, RatedShipment $b) => $a->TotalCharges > $b->TotalCharges);
        return $this->render('cart/shipping.html.twig', ['current' => $address, 'error' => $error, 'rates' => $rates, 'shippingAddress' => $shippingAddress, 'shippingAddressForm' => $shippingAddressType->createView()]);
    }

    #[Route('/cart/update', name: 'cart_update')]
    public function updateAction(Request $request): RedirectResponse
    {
        $productRegistry = $this->managerRegistry->getRepository(\App\Entity\Product::class);

        foreach ($request->request->all('product') as $productId => $quantity) {
            $product = $productRegistry->find(intval($productId));
            $this->cart->update($product, intval($quantity));
        }

        $this->cart->save();
        return $this->redirectToRoute('cart');
    }
}
