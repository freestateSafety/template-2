<?php
namespace App\Request\ParamConverter;

use App\Entity\ProductCategory;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductCategoryParamConverter implements ParamConverterInterface
{
    /**
     * ProductCategoryParamConverter constructor.
     */
    public function __construct(private readonly ManagerRegistry $registry)
    {
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        // Get actual entity manager for class
        $em = $this->registry->getManagerForClass($configuration->getClass());

        /** @var \App\Repository\ProductCategoryRepository $categoryRepository Product category repository */
        $categoryRepository = $em->getRepository($configuration->getClass());
        $productCategory = null;

        // Updated this to work with admin area and how it saves
        if ($request->attributes->has('id')) {
            $productCategory = $categoryRepository->find($request->attributes->get('id'));
        } else {
            $parentId = $request->attributes->get('parent_id');
            $categoryId = $request->attributes->get('category_id');

            // Check, if route attributes exists
            if (null === $parentId || null === $categoryId) {
                throw new \InvalidArgumentException('Route attribute is missing');
            }

            // Try to find village by its slug and slug of its district
            $productCategory = $categoryRepository->findByParentAndCategory($parentId, $categoryId);
        }

        if (null === $productCategory || !($productCategory instanceof ProductCategory)) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $configuration->getClass()));
        }

        // Map found village to the route's parameter
        $request->attributes->set($configuration->getName(), $productCategory);
        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        // If there is no manager, this means that only Doctrine DBAL is configured
        // In this case we can do nothing and just return
        if (null === $this->registry || !count($this->registry->getManagers())) {
            return false;
        }

        // Check, if option class was set in configuration
        if (null === $configuration->getClass()) {
            return false;
        }

        // Get actual entity manager for class
        $em = $this->registry->getManagerForClass($configuration->getClass());

        // Check, if class name is what we need
        if (!$em || \App\Entity\ProductCategory::class !== $em->getClassMetadata($configuration->getClass())->getName()) {
            return false;
        }

        return true;
    }
}
