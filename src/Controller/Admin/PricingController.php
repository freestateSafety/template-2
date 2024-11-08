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

class PricingController extends AbstractController
{

    #[Route('/admin/pricing/', name: 'admin_pricing', methods: ['GET'])]
    public function indexAction(): Response
    {
        return $this->render('admin/pricing/index.html.twig');
    }

    #[Route('/admin/pricing/add', name: 'admin_pricing_add', methods: ['GET'])]
    public function addTableAction(): Response
    {
        return $this->render('admin/pricing/add-table.html.twig');
    }

    #[Route('/admin/pricing/edit', name: 'admin_pricing_edit', methods: ['GET'])]
    public function editTableAction(): Response
    {
        return $this->render('admin/pricing/edit-table.html.twig');
    }
    #[Route('/admin/pricing/associate', name: 'admin_pricing_assocaite', methods: ['GET'])]
    public function associateTableAction(): Response
    {
        return $this->render('admin/pricing/associate.html.twig');
    }
}
