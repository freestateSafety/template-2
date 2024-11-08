<?php
namespace App\Controller\Admin\Products;

use App\Controller\AbstractController;
use App\Entity\ProductCategory;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CategoriesController extends AbstractController
{
    #[Route('/admin/products/categories', name: 'admin_products_categories_create', methods: ['POST'])]
    public function createAction(Request $request): RedirectResponse
    {
        $form = $this->createForm(\App\Form\Type\ProductCategoryType::class, null);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            throw new BadRequestHttpException();
        }

        /** @var ProductCategory $category */
        $category = $form->getData();

        if ($form->isValid()) {
            $em = $this->managerRegistry->getManager();
            $repository = $em->getRepository(ProductCategory::class);
            // set the priority, default to end
            $category->setPriority($repository->getMaxPriority($category->getParent())+1);
            $em->persist($category);
            $em->flush();
            $repository->refreshPriority($category->getParent());
            $this->addFlash('success', sprintf('Added new product category %s (%d)', $category->getLabel(), $category->getId()));
        } else {
            $this->addFlash('error', (string)$form->getErrors(true));
        }

        if ($category->getParent()) {
            return $this->redirectToRoute('admin_products_categories_show', ['id' => $category->getParent()->getId()]);
        }

        return $this->redirectToRoute('admin_products_categories');
    }

    #[Route('/admin/products/categories/{id}', name: 'admin_products_categories_delete', methods: ["DELETE"])]
    public function deleteAction(Request $request, ProductCategory $category): RedirectResponse
    {
        $em = $this->managerRegistry->getManager();
        $repository = $em->getRepository(ProductCategory::class);
        $em->remove($category);
        $em->flush();
        $repository->refreshPriority($category->getParent());
        $this->addFlash('success', 'Removed product category '.$category->getLabel());
        return $this->redirectToRoute('admin_products_categories');
    }

    #[Route('/admin/products/categories', name: 'admin_products_categories', methods: ['GET'])]
    public function indexAction(Request $request): Response
    {
        return $this->render('admin/products/categories/index.html.twig', ['categories' => $this->managerRegistry->getManager()->getRepository(ProductCategory::class)->findBy(
            ['parent' => null],
            ['priority' => 'ASC']
        ), 'form' => $this->createForm(\App\Form\Type\ProductCategoryType::class)->createView()]);
    }

     #[Route('/admin/products/categories/priority', name: 'admin_products_categories_priority', methods: ['PUT'])]
    public function priorityAction(Request $request): RedirectResponse
    {
        $order = array_flip(explode(';', (string) $request->get('order')));
        $em = $this->managerRegistry->getManager();
        $repository = $em->getRepository(ProductCategory::class);
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->in('id', array_keys($order)));

        if  (($parent = $request->get('category')) !== null) {
            $parent = $repository->find($parent);
            $criteria->andWhere(Criteria::expr()->eq('parent', $parent));
        } else {
            $criteria->andWhere(Criteria::expr()->isNull('parent'));
        }

        foreach ($repository->matching($criteria) as $category) {
            $category->setPriority(null);
            $em->merge($category);
        }

        $em->flush();

        /** @var ProductCategory $category */
        foreach ($repository->matching($criteria) as $category) {
            $category->setPriority($order[$category->getId()]+1);
            $em->merge($category);
        }

        $em->flush();
        $this->addFlash('success', 'Updated sort order for categories');

        if (isset($parent)) {
            return $this->redirectToRoute('admin_products_categories_show', ['id' => $parent->getId()]);
        }

        return $this->redirectToRoute('admin_products_categories');
    }

    #[Route('/admin/products/categories/{id}', name: 'admin_products_categories_save', methods: ['PUT'])]
    public function saveAction(Request $request, ?ProductCategory $category): RedirectResponse
    {
        $image = $category->getImage();
        $this->stringToFile($category);
        $form = $this->createForm(\App\Form\Type\ProductCategoryType::class, $category, ['method' => 'PUT']);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            throw new BadRequestHttpException();
        }

        if ($form->isValid()) {
            /** @var ProductCategory $category */
            $category = $form->getData();
            $this->fileToString($category, $image);
            $em = $this->managerRegistry->getManager();
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', sprintf('Saved product category %s (%d)', $category->getLabel(), $category->getId()));
        } else {
            $this->addFlash('error', (string)$form->getErrors(true));
        }

        if ($category->getParent()) {
            return $this->redirectToRoute('admin_products_categories_show', ['id' => $category->getParent()->getId()]);
        }

        return $this->redirectToRoute('admin_products_categories');
    }

    /**
     * @throws \Exception
     */
    #[Route('/admin/products/categories/{id}', name: 'admin_products_categories_show', methods: ['GET'])]
    public function showAction(Request $request, ProductCategory $category): Response
    {
        $criteria = new Criteria();
        $criteria->orderBy(['priority' => Criteria::ASC]);

        if ($category->getParent() !== null) {
            throw new \Exception('Sub categories cannot be edited');
        } elseif ($request->isXmlHttpRequest()) {
            $categories = $category->getSubCategories()->matching($criteria)->toArray();
            return new JsonResponse($categories);
        }

        return $this->render('admin/products/categories/index.html.twig', ['category' => $category, 'categories' => $category->getSubCategories()->matching($criteria), 'form' => $this->createForm(\App\Form\Type\ProductCategoryType::class)->createView()]);
    }
    private function fileToString(ProductCategory &$productCategory, $original = null)
    {
        $file = $productCategory->getImage();
        if ($file instanceof UploadedFile) {
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->getParameter('uploads_dir') . 'categories', $fileName);
            $productCategory->setImage($fileName);
        } elseif ($file instanceof File) {
            $productCategory->setImage($file->getFilename());
        } elseif (!empty($original)) {
            $productCategory->setImage($original);
        }
    }

    private function stringToFile(ProductCategory &$productCategory)
    {
        $image = $productCategory->getImage();

        if (is_string($image)) {
            try {
                $productCategory->setImage(new File($this->getParameter('uploads_dir') . 'categories' . DIRECTORY_SEPARATOR . $image));
            } catch (FileNotFoundException) {
                $productCategory->setImage(null);
            }
        }
    }
}
