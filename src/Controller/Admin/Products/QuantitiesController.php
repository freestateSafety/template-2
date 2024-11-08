<?php
namespace App\Controller\Admin\Products;

use App\Controller\AbstractController;
use App\Entity\ProductQuantity;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuantitiesController extends AbstractController
{
    #[Route('/admin/products/quantities', name: 'admin_products_quantities_create', methods: ['POST'])]
    public function createAction(Request $request): RedirectResponse
    {
        $form = $this->createForm(\App\Form\Type\ProductQuantityType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ProductQuantity $quantity */
            $quantity = $form->getData();
            if ($form->isValid()) {
                $em = $this->managerRegistry->getManager();
                $em->persist($quantity);
                $em->flush();
                $this->addFlash('success', sprintf('Added new product quantity %s (%d)', $quantity->getLabel(), $quantity->getId()));
            } else {
                $this->addFlash('error', (string)$form->getErrors());
            }
        } else {
            return $this->redirectToRoute('admin_products');
        }

        return $this->redirectToRoute('admin_products_update', ['id' => $quantity->getProduct()->getId()]);
    }

    #[Route('/admin/products/quantities/{id}', name: 'admin_products_quantities_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, ProductQuantity $quantity): RedirectResponse
    {
        $em = $this->managerRegistry->getManager();
        $em->remove($quantity);
        $em->flush();
        $this->addFlash('success', sprintf('Removed quantity %s', $quantity->getLabel()));
        return $this->redirectToRoute('admin_products_update', ['id' => $quantity->getProduct()->getId()]);
    }

    #[Route('/admin/products/quantities/{id}', name: 'admin_products_quantities_save', methods: ['PUT'])]
    public function saveAction(Request $request, ProductQuantity $quantity): RedirectResponse
    {
        $options = ['method' => (empty($quantity))? 'POST' : 'PUT'];
        $form = $this->createForm(\App\Form\Type\ProductQuantityType::class, $quantity, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var ProductQuantity $quantity */
            $quantity = $form->getData();
            if ($form->isValid()) {
                $em = $this->managerRegistry->getManager();
                if ($request->getMethod() == Request::METHOD_POST) {
                    $em->persist($quantity);
                } else {
                    $em->merge($quantity);
                }
                $em->flush();
                $this->addFlash('success', sprintf('Updated quantity %s (%d)', $quantity->getLabel(), $quantity->getId()));
            } else {
                $this->addFlash('error', (string)$form->getErrors());
            }
        }

        return $this->redirectToRoute('admin_products_update', ['id' => $quantity->getProduct()->getId()]);
    }
}
