<?php

namespace App\Controller;

use App\Form\Type\ContactType;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/about', name: 'about')]
    public function aboutAction(Request $request)
    {
        return $this->render('default/about.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contactAction(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            
            if ($form->isValid()) {

                $emailText = "";
                $emailText .= "Name: " . $form->get('name')->getData() . "\r\n";
                $emailText .= "Email: " . $form->get('email')->getData() . "\r\n";
                $emailText .= "Phone: " . $form->get('phone')->getData() . "\r\n";
                $emailText .= "Company: " . $form->get('company')->getData() . "\r\n";
                $emailText .= "Comments:\r\n" . $form->get('comments')->getData() . "\r\n";

                $email = (new Email())
                    ->from(new Address($this->getParameter('smtp_email')))
                    ->replyTo(new Address($form->get('email')->getData(), $form->get('name')->getData()))
                    ->to($this->getParameter('contact_email'))
                    ->subject($this->getParameter('site.name') . ' Contact Form')
                    ->text( $emailText )
                ;
                $mailer->send($email);
                $this->addFlash('success', 'Your message has been sent! We will reply as soon as possible.');
                $form = $this->createForm(ContactType::class);
            }
        }

        return $this->render('default/contact.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/', name: 'homepage')]
    public function indexAction(Request $request)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('parent'));
        $criteria->orderBy(['priority' => Criteria::ASC]);

        return $this->render('default/index.html.twig', 
            [
                /* 
                COMMENTED OUT FOR BETTER SOLUTION
                'bannerLarge' => true, 
                'bannerTime' => filemtime($this->getParameter('uploads_dir').'banner.png'), 
                 */
                'categories' => $this->managerRegistry->getManager()->getRepository(\App\Entity\ProductCategory::class)->matching($criteria)
            ]);
    }

    #[Route('/privacy', name: 'privacy')]
    public function privacyAction(Request $request)
    {
        return $this->render('default/privacy.html.twig');
    }
}
