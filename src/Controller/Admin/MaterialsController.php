<?php
namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Material;
use App\Entity\Product;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MaterialsController extends AbstractController
{
    #[Route('/admin/materials', name: 'admin_materials_create', methods: ['POST'])]
    public function createAction(Request $request): RedirectResponse
    {
        $form = $this->createForm(\App\Form\Type\MaterialType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            throw new BadRequestHttpException();
        }

        if ($form->isValid()) {
            /** @var Material $material */
            $material = $form->getData();
            $em = $this->managerRegistry->getManagerForClass(Material::class);
            $em->persist($material);
            $em->flush();
            $this->addFlash('success', 'Added new material (' . $material->getId() . ') ' . $material->getMaterial());
        } else {
            $this->addFlash('error', (string)$form->getErrors());
        }

        return $this->redirectToRoute('admin_materials');
    }

    #[Route('/admin/materials/{id}', name: 'admin_materials_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, Material $material): Response|RedirectResponse
    {
        $em = $this->managerRegistry->getManager();

        if ($material->getProducts()->count() > 0) {
            $repository = $em->getRepository(Material::class);

            if (
                !$request->request->has('new_material') ||
                ($new_material = $repository->find($request->request->get('new_material'))) === null
            ) {
                $criteria = Criteria::create();
                $criteria->where(Criteria::expr()->neq('id', $material->getId()));

                return $this->render('admin/materials/products.html.twig', ['material' => $material, 'materials' => $repository->matching($criteria)]);
            }

            /** @var Product $product */
            foreach ($material->getProducts() as $product) {
                $product->setMaterial($new_material);
                $em->merge($product);
            }

            $em->flush();
        }

        try {
            $em->remove($material);
            $em->flush();
            $this->addFlash('success', 'Removed material ' . $material->getMaterial());
        } catch (\Exception) {
            $this->addFlash('error', 'Failed to remove material ' . $material->getMaterial());
        }

        return $this->redirectToRoute('admin_materials');
    }

    #[Route('/admin/materials', name: 'admin_materials', methods: ['GET'])]
    public function indexAction(Request $request): Response
    {
        return $this->render('admin/materials/index.html.twig', ['form' => $this->createForm(\App\Form\Type\MaterialType::class)->createView(), 'materials' => $this->managerRegistry->getManager()->getRepository(Material::class)->findAll()]);
    }

    #[Route('/admin/materials/{id}', name: 'admin_materials_show', methods: ['GET', 'POST'])]
    public function showAction(Request $request, Material $material): Response|RedirectResponse
    {
        $em = $this->managerRegistry->getManager();
        if (
            $request->getMethod() === Request::METHOD_POST && $request->request->has('parent') &&
            ($parent = $em->getRepository(Material::class)->find($request->request->get('parent'))) !== null
        ) {
            $products = [];
            foreach ($request->request->get('products') as $product) {
                $product = $em->getRepository(Product::class)->find($product);
                if ($product->getMaterial() !== $material) continue;
                $product->setMaterial($parent);
                $products[] = $product;
                $em->merge($product);
            }

            $em->flush();
            $this->addFlash('success', 'Assigned ' . count($products) . ' products from "' . $material->getMaterial() . '" to "' . $parent->getMaterial() . '"');
            return $this->redirectToRoute('admin_materials_show', ['id' => $material->getId()]);
        }

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->neq('id', $material->getId()));

        return $this->render('admin/materials/show.html.twig', ['material' => $material, 'materials' => $em->getRepository(Material::class)->matching($criteria)]);
    }

    #[Route('/admin/materials/{id}', name: 'admin_materials_save', methods: ['PUT'])]
    public function saveAction(Request $request, Material $material): RedirectResponse
    {
        $form = $this->createForm(\App\Form\Type\MaterialType::class, $material, ['method' => 'PUT']);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            throw new BadRequestHttpException();
        }

        if ($form->isValid()) {
            /** @var Material $material */
            $material = $form->getData();
            $em = $this->managerRegistry->getManager();
            $em->merge($material);
            $em->flush();
            $this->addFlash('success', 'Saved material ' . $material->getMaterial());
        } else {
            $this->addFlash('error', (string)$form->getErrors());
        }

        return $this->redirectToRoute('admin_materials');
    }
}
