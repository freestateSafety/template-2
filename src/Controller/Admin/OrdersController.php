<?php
namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Order;
use App\Entity\Payment;
use Defuse\Crypto\Key;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class OrdersController extends AbstractController
{
    #[Route('/admin/orders/active', name: 'admin_orders_active')]
    public function activeAction(Request $request): Response
    {
        return $this->render('admin/orders/active.html.twig', ['orders' => $this->managerRegistry->getManager()->getRepository(Order::class)->findBy(['deleted' => false, 'fulfilled' => false])]);
    }

    #[Route('/admin/orders/{id}', name: 'admin_orders_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, Order $order): RedirectResponse
    {
        // we don't really delete the record
        $order->setDeleted(true);
        $em = $this->managerRegistry->getManager();
        $em->merge($order);
        $em->flush();
        $this->addFlash('success', 'Marked order ' . $order->getId() . ' as deleted');
        return $this->redirectToRoute('admin_orders_show', ['id' => $order->getId()]);
    }

    #[Route('/admin/orders/deleted', name: 'admin_orders_deleted')]
    public function deletedAction(Request $request): Response
    {
        return $this->render('admin/orders/deleted.html.twig', ['orders' => $this->managerRegistry->getManager()->getRepository(Order::class)->findBy(['deleted' => true])]);
    }

    #[Route('/admin/orders/fulfilled', name: 'admin_orders_fulfilled')]
    public function fulfilledAction(Request $request): Response
    {
        $orders = $this->managerRegistry->getManager()->getRepository(Order::class)->findFulfilledOrders(
            $request->query->getInt('month'),
            $request->query->getInt('year')
        );

        return $this->render('admin/orders/fulfilled.html.twig', ['month' => $request->query->getInt('month', date('m')), 'orders' => $orders, 'year' => $request->query->getInt('year', date('Y'))]);
    }

    #[Route('/admin/orders/{id}', name: 'admin_orders_save', methods: ['PUT'])]
    public function saveAction(Request $request, Order $order, MailerInterface $mailer): RedirectResponse
    {
        if (($complete = $request->get('complete')) !== null) {
            $order->setFulfilled(true);
            $this->addFlash('success', 'Order '.$order->getNumber().' has been marked fulfilled');
        }

        if (($notes = $request->get('notes')) !== null) {
            $order->setNotes($request->get('notes'));
        }

        $em = $this->managerRegistry->getManager();

        if (($tracking = $request->get('tracking_number')) !== null) {
            $shipment = $order->getShipment();
            $shipment->setTrackingNumber($request->get('tracking_number'));
            $em->merge($shipment);
        }

        $em->merge($order);
        $em->flush();

        if ($complete === 'email') {
            $email = $this->render('email/order_shipped.html.twig', ['order' => $order]);
            $message = (new Email())
                ->subject($this->getParameter('site.name') . ' Order has been Fulfilled')
                ->from(new Address($this->getParameter('smtp_email'), $this->getParameter('site.name')))
                ->replyTo(new \Symfony\Component\Mime\Address($this->getParameter('order_email'), $this->getParameter('site.name')))
                ->to(new Address($order->getCustomer()->getEmail(), $order->getCustomer()->getName()))
                ->html($email->getContent());
            $mailer->send($message);
        }

        return $this->redirectToRoute('admin_orders_active');
    }

    #[Route('/admin/orders/{id}', name: 'admin_orders_show', methods: ['GET'])]
    public function showAction(Request $request, Order $order): Response
    {
        $key = Key::loadFromAsciiSafeString($this->getParameter('secret.key'));

        return $this->render('admin/orders/order.html.twig', ['creditCard' => (($order->getPayment()->getMethod() === Payment::METHOD_CARD)? $order->getPayment()->getCreditCard($key) : null), 'order' => $order]);
    }
}
