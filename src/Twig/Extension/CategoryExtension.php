<?php
namespace App\Twig\Extension;

use Doctrine\Persistence\ManagerRegistry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategoryExtension extends AbstractExtension
{
    /**
     * CategoryExtension constructor.
     */
    public function __construct(private readonly ManagerRegistry $registry)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [new TwigFunction('categories', fn() => $this->registry->getRepository(\App\Entity\ProductCategory::class))];
    }

    public function getName()
    {
        return 'app.twig.extension.category';
    }
}
