<?php

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\Customer;
use App\Entity\OrderProduct;
use App\Model\ChangePassword;
use App\Service\CartService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DashboardController extends AbstractController
{
    #[Route('/user/dashboard', name: 'user_dashboard')]
    public function dashboardAction(): Response
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('deleted', false))
            ->orderBy(['created' => Criteria::DESC])
            ->setMaxResults(10);

        return $this->render('user/dashboard.html.twig', ['orders' => $this->getUser()->getOrders()->matching($criteria), 'user' => $this->getUser()]);
    }

    #[Route('/user/order/{number}', name: 'user_order')]
    public function orderAction(Request $request, string $number, CartService $cart): Response|RedirectResponse
    {
        @[$id, $created] = explode('-', $number);

        try {
            $start = new \DateTime('20' . $created, new \DateTimeZone('UTC'));
            $start->setTimezone(new \DateTimeZone(ini_get('date.timezone')));
            $end = clone $start;
            $end->add(new \DateInterval('PT1M'));

            $repository = $this->managerRegistry->getRepository(\App\Entity\Order::class);
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq('id', $id))
                ->andWhere(Criteria::expr()->gte('created', $start))
                ->andWhere(Criteria::expr()->lt('created', $end));

            $order = $repository->matching($criteria)->first();
        } catch (\Exception) {
            $order = false;
        }

        if (!$order) {
            throw new NotFoundHttpException('Unable to find order ' . $number);
        }

        if ($request->query->has('duplicate')) {
            /** @var OrderProduct $product */
            foreach ($order->getProducts() as $product) {
                if ($product->getProduct() != null) {
                    $cart->add($product->getProduct(), $product->getQuantity());
                }
            }

            if ($cart->length() == 0) {
                $this->addFlash('error', 'Unable to find original products to recreate order, please contact us.');
                return $this->redirectToRoute('user_order', ['orderId' => $order->id]);
            }

            $cart->save();
            $this->addFlash('notice', 'Successfully duplicated order');
            return $this->redirectToRoute('cart');
        }

        if ($order->getCustomer() !== $this->getUser()) {
            echo 'fail';
        }

        return $this->render('user/order.html.twig', ['order' => $order]);
    }

    #[Route('/user/password', name: 'user_password')]
    public function passwordAction(Request $request, TokenStorageInterface $tokenStorage, PasswordHasherFactoryInterface $passwordHasherFactory): Response|RedirectResponse
    {
        $form = $this->createForm(\App\Form\Type\PasswordResetType::class, new ChangePassword());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->getData();
            $user = $tokenStorage->getToken()->getUser();
            $password = $passwordHasherFactory->getPasswordHasher($user)->hash($password->getPlainPassword());
            $user->setPassword($password);
            $em = $this->managerRegistry->getManager();
            $em->merge($user);
            $em->flush();
            $this->addFlash('success', 'You have successfully changed your password');
            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('user/password.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/user/profile', name: 'user_profile')]
    public function profileAction(Request $request, ManagerRegistry $managerRegistry): Response
    {
        /** @var Customer $customer */
        $customer = $this->getUser();
        $customerForm = $this->createForm(\App\Form\Type\CustomerType::class, $customer);
        $customerForm->handleRequest($request);

        if ($customerForm->isSubmitted() && $customerForm->isValid()) {
            $customer = $customerForm->getData();
            $em = $managerRegistry->getManager();
            $em->merge($customer);
            $em->flush();
            $this->addFlash('notice', 'Your profile information has been updated');
        }

        return $this->render('user/profile.html.twig', ['form' => $customerForm->createView()]);
    }
}
