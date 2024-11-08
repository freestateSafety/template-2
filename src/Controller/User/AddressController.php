<?php
namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\Address;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddressController extends AbstractController
{
    #[Route('/user/address/create/{type}', name: 'user_address_create', methods: ['GET', 'POST'])]
    public function createAction(Request $request, $type): Response|RedirectResponse
    {
        $address = new Address();
        if (($addressForm = $this->_createAddressForm($type, $address)) === false) {
            $this->addFlash('error', 'Cannot create new address. Unknown type of address "' . (string)$type . '"');
            return $this->redirectToRoute('user_dashboard');
        }

        $addressForm->handleRequest($request);

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $address->setType($type);
            $address->setCustomer($this->getUser());
            $em = $this->managerRegistry->getManager();
            $em->persist($address);
            $em->flush();
            $this->addFlash('success', 'Created new '.(string)$type.' address: '.(string)$address);
            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('user/address/form.html.twig', ['address' => $address, 'addressType' => $type, 'form' => $addressForm->createView()]);
    }

    #[Route('/user/address/{id}', name: 'user_address_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, Address $address): RedirectResponse
    {
        if ($this->getUser()->getAddresses($address->getType())->count() == 1) {
            $this->addFlash('error', 'Unable to delete only available '.$address->getType().' address');
        } else {
            $em = $this->managerRegistry->getManager();
            $em->remove($address);
            $em->flush();
            $this->addFlash('success', 'Removed ' . $address->getType() . ' address: ' . (string)$address);
        }

        return $this->redirectToRoute('user_dashboard');
    }

    #[Route('/user/address/{type}/{id}', name: 'user_address_update', methods: ['GET', 'PUT'])]
    public function updateAction(Request $request, string $type, Address $address)
    {
        $args = ['id' => $address->getId(), 'type' => $address->getType()];

        if ($type !== $address->getType()) {
            $this->addFlash('error', 'Unable to change address type');
            return $this->redirectToRoute('user_address_update', $args);
        } elseif (($addressForm = $this->_createAddressForm($type, $address, Request::METHOD_PUT)) === false) {
            $this->addFlash('error', 'Unknown type of address "' . (string)$type . '"');
            return $this->redirectToRoute('user_address_update', $args);
        } elseif ($addressForm->handleRequest($request)->isSubmitted() && $addressForm->isValid()) {
            $address = $addressForm->getData();
            $em = $this->managerRegistry->getManager();
            $em->merge($address);
            $em->flush();
            $this->addFlash('success', 'Updated '.$type.' address');
            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('user/address/form.html.twig', ['address' => $address, 'addressType' => $type, 'form' => $addressForm->createView()]);
    }

    private function _createAddressForm($type, Address $address, $method = Request::METHOD_POST)
    {
        switch ($type) {
            case Address::TYPE_BILLING:
                $addressForm = $this->createForm(\App\Form\Type\AddressBillingType::class, $address, ['method' => $method]);
                break;

            case Address::TYPE_SHIPPING:
                $addressForm = $this->createForm(\App\Form\Type\AddressShippingType::class, $address, ['method' => $method]);
                break;

            default:
                return false;
        }

        return $addressForm;
    }
}
