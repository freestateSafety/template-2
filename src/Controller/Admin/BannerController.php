<?php
namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\BannerImage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BannerController extends AbstractController
{
    #[Route('/admin/banner', name: 'admin_banner')]
    public function indexAction(Request $request)
    {
        /** @var BannerImage $bannerImage */
        $bannerImage = new BannerImage();
        $bannerForm = $this->createForm(\App\Form\Type\BannerType::class, $bannerImage);
        $bannerForm->handleRequest($request);

        if ($bannerForm->isSubmitted() && $bannerForm->isValid()) {
            $file = $bannerImage->getBannerImage();
            $file->move($this->getParameter('uploads_dir'), 'banner.png');
            $this->addFlash('success', 'Successfully updated banner image');
        }

        return $this->render('admin/banner/index.html.twig', ['bannerForm' => $bannerForm->createView(), 'bannerTime' => filemtime($this->getParameter('uploads_dir').'banner.png')]);
    }
}
