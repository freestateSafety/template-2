<?php
namespace App\Controller\Admin;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function indexAction(): Response
    {
        return $this->render('admin/default/index.html.twig');
    }
}
