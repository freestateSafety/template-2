<?php
namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Http\CsvResponse;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{
    final public const MAX_BATCH_SIZE = 50;

    #[Route('/admin/products/export', name: 'admin_products_export', methods: ['GET', 'POST'])]
    public function exportAction(Request $request): CsvResponse
    {
        $productRepository = $this->managerRegistry->getManager()->getRepository(Product::class);
        $criteria = Criteria::create();

        if ($request->isMethod(Request::METHOD_POST)) {
        }

        $criteria->orderBy(['name' => Criteria::ASC]);
        $products = array_map(fn($product) =>
            /** @var Product $product */
            ['id' => $product->getId(), 'class' => $product->getClass(), 'name' => $product->getName(), 'itemNumber' => $product->getItemNumber(), 'size' => $product->getSize(), 'shape' => $product->getShape(), 'quantity' => $product->getQuantity(), 'weight' => $product->getWeight(), 'viewable' => $product->getViewable(), 'notes' => $product->getNotes()], $productRepository->matching($criteria)->toArray());
        return (new CsvResponse($products))->setFilename('products.csv');
    }

    #[Route('/admin/products/import', name: 'admin_products_import', methods: ['DELETE', 'POST'])]
    public function importAction(Request $request): RedirectResponse
    {
        $processed = $updated = $removed = $missed = 0;
        $delete = ($request->getMethod() === 'DELETE');
        $em = $this->managerRegistry->getManager();
        $productRepository = $em->getRepository(Product::class);
        /** @var File $file */
        $file = $request->files->get('import');
        $fp = $file->openFile();
        $fp->setFlags(\SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        while ($row = $fp->fgetcsv()) {
            /** @var Product $product */
            if (($product = $productRepository->find($row[0])) !== null) {
                if ($delete) {
                    $em->remove($product);
                    $removed++;
                } else {
                    $product->setClass($row[1]);
                    $product->setName($row[2]);
                    $product->setItemNumber($row[3]);
                    $product->setSize($row[4]);
                    $product->setShape($row[5]);
                    $product->setQuantity($row[6]);
                    $product->setWeight((float)$row[7]);
                    $product->setViewable((bool)$row[8]);
                    $product->setNotes($row[9]);
                    $em->merge($product);
                    $updated++;
                }
            } else {
                $missed++;
            }

            // batch things, just in case
            if ($processed++ % static::MAX_BATCH_SIZE) {
                $em->flush();
                $em->clear();
            }
        }

        $em->flush();
        $this->addFlash('success', sprintf('Imported %s products with %s missed and %s', $processed, $missed, ($delete)? $removed.' deleted' : $updated.' updated'));
        return $this->redirectToRoute('admin_products_manage');
    }

    #[Route('/admin/products/manage', name: 'admin_products_manage', methods: ['GET'])]
    public function manageAction(Request $request): Response
    {
        return $this->render('admin/products/manage.html.twig');
    }

    #[Route('/admin/products/{parent_id}/{id}', name: 'admin_products_create', methods: ['GET', 'POST'])]
    public function createAction(Request $request, ProductCategory $productCategory): Response|RedirectResponse
    {
        $product = new Product();
        $product->setProductCategory($productCategory);
        $productForm = $this->createForm(\App\Form\Type\ProductType::class, $product, ['action' => $this->generateUrl('admin_products_create', ['parent_id' => $productCategory->getParent()->getId(), 'id' => $productCategory->getId()]), 'method' => 'POST']);
        $productForm->handleRequest($request);

        if ($productForm->isSubmitted() && $productForm->isValid()) {
            $this->fileToString($product);
            $em = $this->managerRegistry->getManagerForClass(Product::class);
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Added new product ' . $product->getName());
            return $this->redirectToRoute('admin_products_update', ['id' => $product->getId()]);
        }

        return $this->render('admin/products/show.html.twig', ['currentCategory' => $product->getCategory(), 'currentSubCategory' => $product->getProductCategory(), 'materials' => $this->managerRegistry->getManager()->getRepository(\App\Entity\Material::class)->findAll(), 'product' => $product, 'productForm' => $productForm->createView(), 'quantitiesForm' => $this->createForm(\App\Form\Type\ProductQuantityType::class)->createView()]);
    }

    /**
     * This method assumes the user already confirmed deletion.
     */
    #[Route('/admin/products/{id}', name: 'admin_products_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, Product $product): Response
    {
        $id = $product->getId();
        $name = $product->getName();
        $em = $this->managerRegistry->getManager();
        $em->remove($product);
        $em->flush();
        $this->addFlash('success', 'Removed product ' . $product->getName() .' (' . $product->getId() . ')');

        return $this->redirectToRoute('admin_products');
    }

    #[Route('/admin/products/image/{id}', name: 'admin_products_image', methods: ['POST'])]
    public function imageAction(Request $request, Product $product): Response
    {
    }

    #[Route('/admin/products', name: 'admin_products', methods: ['GET', 'POST'])]
    public function indexAction(Request $request): Response|RedirectResponse
    {
        $productCategories = $this->managerRegistry->getManager()->getRepository(ProductCategory::class);
        $currentCategory = $request->query->get('category', $productCategories->findOneBy(['parent' => null], ['priority' => Criteria::ASC])->getId());
        $currentCategory = $productCategories->find($currentCategory);
        $subCrit = Criteria::create();
        $subCrit->where(Criteria::expr()->eq('parent', $currentCategory))->orderBy(['priority' => Criteria::ASC]);
        $currentSubCategory = $request->query->get('subCategory', $productCategories->matching($subCrit)->first()->getId());
        $currentSubCategory = $productCategories->find($currentSubCategory);

        if ($request->request->has('new_category') && ($newCategory = $productCategories->find($request->request->get('new_category'))) !== null) {
            $em = $this->managerRegistry->getManager();
            /** @var Product $product */
            foreach ($currentSubCategory->getProducts() as $product) {
                $product->setProductCategory($newCategory);
                $em->merge($product);
            }

            $em->flush();
            $this->addFlash('success', 'Moved all products from ' . $currentSubCategory->getLabel() . ' to ' . $newCategory->getLabel());

            return $this->redirectToRoute('admin_products', ['category' => $newCategory->getParent()->getId(), 'subCategory' => $newCategory->getId()]);
        }

        $products = $currentSubCategory->getProducts();
        $pages = array_chunk($products->toArray(), 20);

        return $this->render('admin/products/index.html.twig', ['categories' => $productCategories->findAll(), 'currentCategory' => $currentCategory, 'currentSubCategory' => $currentSubCategory, 'pages' => $pages, 'products' => $products]);
    }

    #[Route('/admin/products/{id}', name: 'admin_products_update', methods: ['GET', 'PUT'])]
    public function updateAction(Request $request, Product $product): Response|RedirectResponse
    {
        $image = $product->getImage();
        $this->stringToFile($product);
        $form = $this->createForm(\App\Form\Type\ProductType::class, $product, ['action' => $this->generateUrl('admin_products_update', ['id' => $product->getId()]), 'method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->fileToString($product, $image);
            $em = $this->managerRegistry->getManager();
            $em->merge($product);
            $em->flush();
            $this->addFlash('success', 'Updated product ' . $product->getName() .' (' . $product->getId() . ')');
            return $this->redirectToRoute('admin_products', ['category' => $product->getCategory()->getId(), 'subCategory' => $product->getProductCategory()->getId()]);
        }

        return $this->render('admin/products/show.html.twig', ['currentCategory' => $product->getCategory(), 'currentSubCategory' => $product->getProductCategory(), 'image' => $image, 'product' => $product, 'productForm' => $form->createView(), 'quantitiesForm' => $this->createForm(\App\Form\Type\ProductQuantityType::class)->createView()]);
    }

    private function fileToString(Product &$product, $original = null)
    {
        $file = $product->getImage();
        if ($file instanceof UploadedFile) {
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->getParameter('uploads_dir') . 'products', $fileName);
            $product->setImage($fileName);
        } elseif ($file instanceof File) {
            $product->setImage($file->getFilename());
        } elseif ($original) {
            $product->setImage($original);
        }
    }

    private function stringToFile(Product &$product)
    {
        $image = $product->getImage();

        if (is_string($image)) {
            try {
                $product->setImage(new File($this->getParameter('uploads_dir') . 'products' . DIRECTORY_SEPARATOR . $image));
            } catch (FileNotFoundException) {
                $product->setImage(null);
            }
        }
    }
}
