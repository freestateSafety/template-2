<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    final public const PAGE_LIMIT = 20;

    #[Route('/product/search', name: 'product_search', methods: ['GET', 'POST'])]
    public function searchAction(Request $request): Response
    {
        $productSearchType = $this->createForm(\App\Form\Type\ProductSearchType::class);
        $productSearchType->handleRequest($request);
        $pages = null;
        $products = [];

        if ($productSearchType->isSubmitted() && $productSearchType->isValid()) {
            [$q, $limit] = array_values($productSearchType->getData());

            /** @var QueryBuilder $query */
            $query = $this->managerRegistry->getManager()->getRepository(Product::class)->createQueryBuilder('p');

            if ($request->isXmlHttpRequest()) {
                $query->setMaxResults($limit);
            }

            $products = $query
                ->where('p.itemNumber LIKE :item_number OR p.name LIKE :q OR p.notes LIKE :q')
                ->orderBy('material')
                ->setParameter('item_number', $q.'%')
                ->setParameter('q', '%'.$q.'%')
                ->getQuery()
                ->getResult()
            ;

            if ($request->isXmlHttpRequest()) {
                $products = array_map(fn($product) =>
                    /** @var Product $product */
                    $product->toArray(), $products);
                return new JsonResponse($products);
            }

            $pages = array_chunk($products, 20);
        }

        return $this->render('product/search.html.twig', ['productSearchForm' => $productSearchType->createView(), 'pages' => $pages, 'products' => new ArrayCollection($products)]);
    }

    #[Route('/product/{id}', name: 'product_item')]
    public function indexAction(Request $request, Product $product): Response
    {
        return $this->render(
            (($request->get('overlay'))? 'product/overlay.html.twig' : 'product/item.html.twig'),
            ['product' => $product]
        );
    }

    #[Route('/product/{parent_id}/{id}', name: 'product_list')]
    public function listAction(Request $request, ProductCategory $parentCategory, ProductCategory $productCategory): Response
    {
        $em = $this->managerRegistry->getManager();
        $products = $productCategory->getProducts();

        if ($request->query->has('material') && $request->query->get('material')) {
            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->eq('material', $this->managerRegistry->getRepository(\App\Entity\Material::class)->find($request->query->get('material'))));
            $products = $products->matching($criteria);
        }

        $pages = array_chunk($products->toArray(), 20);
        $stmnt = $em->getConnection()->prepare(
            'SELECT material_id FROM product WHERE product_category_id = ?'
        );
        $materials = $stmnt->execute([$productCategory->getId()])->fetchAll(\PDO::FETCH_COLUMN);

        return $this->render('product/list.html.twig', ['materials' => $this->managerRegistry->getRepository(\App\Entity\Material::class)->findBy(['id' => $materials]), 'pages' => $pages, 'productCategory' => $productCategory, 'products' => $products]);
    }
}
