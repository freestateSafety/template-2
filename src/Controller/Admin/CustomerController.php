<?php
namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Address;
use App\Entity\Customer;
use Defuse\Crypto\KeyProtectedByPassword;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
    #[Route('/admin/customer', name: 'admin_customer', methods: ['GET'])]
    public function indexAction(Request $request): Response
    {
        $criteria = new Criteria();
        $criteria->orderBy(['created' => Criteria::ASC]);

        if ($request->query->has('text')) {
            if ($request->query->has('field') && $request->query->get('field') === 'email') {
                $criteria->where(Criteria::expr()->contains('email', $request->query->get('text')));
            } elseif ($request->query->has('field') && $request->query->get('field') === 'name') {
                $criteria->where(Criteria::expr()->contains('firstName', $request->query->get('name')))
                    ->orWhere(Criteria::expr()->contains('lastName', $request->query->get('name')));
            } elseif ($request->query->has('field') && $request->query->get('field') === 'company') {
                $criteria->where(Criteria::expr()->contains('company', $request->query->get('company')));
            }
        }

        $customerType = $this->createForm(\App\Form\Type\CustomerType::class, null, ['method' => Request::METHOD_POST]);

        return $this->render('admin/customer/index.html.twig', ['customers' => $this->managerRegistry->getManager()->getRepository(Customer::class)->matching($criteria), 'customerForm' => $customerType->createView()]);
    }

    #[Route('/admin/customer/create', name: 'admin_customer_create')]
    public function createAction(Request $request): Response
    {
        $customer = new Customer();
        $billingAddress = new Address(Address::TYPE_BILLING);
        $customer->addAddress($billingAddress);
        $shippingAddress = new Address(Address::TYPE_SHIPPING);
        $customer->addAddress($shippingAddress);
        
        $customerType = $this->createForm(\App\Form\Type\RegisterType::class, $customer);
        $billingAddressType = $this->createForm(\App\Form\Type\AddressBillingType::class, $billingAddress);
        $shippingAddressType = $this->createForm(\App\Form\Type\AddressShippingType::class, $shippingAddress);
        
        return $this->render('admin/customer/form.html.twig', ['customer' => $customer, 'customerForm' => $customerType->createView(), 'billingForm' => $billingAddressType->createView(), 'shippingForm' => $shippingAddressType->createView()]);
    }

    #[Route('/admin/customer/role', name: 'admin_customer_role')]
    public function roleAction(Request $request): Response
    {
        $roles = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];

        $criteria = Criteria::create();
        $criteria
            ->where(Criteria::expr()->in('role', $roles))
            ->orderBy(['role' => 'DESC', 'email' => 'DESC'])
        ;

        return $this->render('admin/customer/role.html.twig', ['customerForm' => $this->createForm(\App\Form\Type\CustomerType::class)->createView(), 'roles' => $roles, 'users' => $this->managerRegistry->getRepository(Customer::class)->matching($criteria)]);
    }

    #[Route('/admin/customer/{id}', name: 'admin_customer_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, Customer $customer): RedirectResponse
    {
        $em = $this->managerRegistry->getManager();
        $em->remove($customer);
        $em->flush();
        $this->addFlash('success', 'Removed customer '.$customer->getName());
        return $this->redirectToRoute('admin_customer');
    }

    #[
        Route('/admin/customer', name: 'admin_customer_create_save', methods: ['POST', 'PUT', 'PATCH']),
        Route('/admin/customer/{id}', name: 'admin_customer_save', methods: ['POST', 'PUT', 'PATCH'])
    ]
    public function saveAction(Request $request, ?Customer $customer, PasswordHasherFactoryInterface $passwordHasherFactory): RedirectResponse
    {
        if (is_null($customer)) {
            $method = 'POST';
            $customerForm = $this->createForm(\App\Form\Type\RegisterType::class, $customer, ['method' => $method]);
            $customer = new Customer();
            $billingAddress = new Address(Address::TYPE_BILLING);
            $shippingAddress = new Address(Address::TYPE_SHIPPING);
        } else {
            $method = $request->request->get('_method');
            $customerForm = $this->createForm(\App\Form\Type\CustomerType::class, $customer, ['method' => $method]);
            $billingAddress = $customer->getAddressesBilling()->first();
            $shippingAddress = $customer->getAddressesShipping()->first();
        }
        
        $billingForm = $this->createForm(\App\Form\Type\AddressBillingType::class, $billingAddress, ['method' => $method]);
        $shippingForm = $this->createForm(\App\Form\Type\AddressShippingType::class, $shippingAddress, ['method' => $method]);
        $customerForm->handleRequest($request);
        $billingForm->handleRequest($request);
        $shippingForm->handleRequest($request);

        if (
            $customerForm->isSubmitted() && $customerForm->isValid()
            && (($billingForm->isSubmitted() && $billingForm->isValid()) || !$billingForm->isSubmitted())
            && (($shippingForm->isSubmitted() && $shippingForm->isValid()) || !$shippingForm->isSubmitted())
        ) {
            $customer = $customerForm->getData();
            $billingAddress = $billingForm->getData();
            $shippingAddress = $shippingForm->getData();
            
            if ($customer->getId() === null) {
                $password = $passwordHasherFactory->getPasswordHasher($customer)->hash($customer->getPlainPassword());
                $customer->setPassword($password);
                $customer->setKey(KeyProtectedByPassword::createRandomPasswordProtectedKey($password));
            }
            
            $em = $this->managerRegistry->getManager();
            $em->persist($customer);

            if ($billingForm->isSubmitted() && $billingForm->isValid()) {
                $billingAddress->setCustomer($customer);
                $em->persist($billingAddress);
            }

            if ($shippingForm->isSubmitted() && $shippingForm->isValid()) {
                $shippingAddress->setCustomer($customer);
                $em->persist($shippingAddress);
            }

            $em->flush();
            $this->addFlash('success', 'Customer record stored successfully');
        } elseif ($customerForm->isSubmitted() || $billingForm->isSubmitted() || $shippingForm->isSubmitted()) {
            $errors = '';

            if (!$customerForm->isValid()) {
                $errors .= (string)$customerForm->getErrors(true, false);
            }

            if ($billingForm->isSubmitted() && !$billingForm->isValid()) {
                $errors .= (string)$billingForm->getErrors(true, false);
            }

            if ($shippingForm->isSubmitted() && !$shippingForm->isValid()) {
                $errors .= (string)$shippingForm->getErrors(true, false);
            }

            if (!empty($errors)) {
                $this->addFlash('error', $errors);
            }
        }

        if ($method === 'PATCH' && $request->headers->has('referer')) {
            return $this->redirect($request->headers->get('referer'));
        } else if ($method === 'POST' && $customer->getId() !== null) {
            // redirect back to dashboard when creating a customer
            return $this->redirectToRoute('admin_customer');
        }
        
        return ($customer->getId() === null)?
            $this->redirectToRoute('admin_customer_create')
            :
            $this->redirectToRoute('admin_customer_show', ['id' => $customer->getId()]);
    }

    #[Route('/admin/customer/{id}', name: 'admin_customer_show', methods: ['GET'])]
    public function showAction(Request $request, Customer $customer): Response
    {
        $customerForm = $this->createForm(\App\Form\Type\CustomerType::class, $customer);
        $billingForm = $this->createForm(\App\Form\Type\AddressBillingType::class, $customer->getAddressesBilling()->first());
        $shippingForm = $this->createForm(\App\Form\Type\AddressShippingType::class, $customer->getAddressesShipping()->first());

        return $this->render('admin/customer/form.html.twig', ['billingForm' => $billingForm->createView(), 'customerForm' => $customerForm->createView(), 'customer' => $customer, 'shippingForm' => $shippingForm->createView()]);
    }
}
