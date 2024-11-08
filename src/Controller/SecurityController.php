<?php
namespace App\Controller;

use App\Entity\PasswordToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($request->hasSession() && $request->getSession()->get('loginFailure', 0) >= 5) {
            $session = $request->getSession();
            $tmp = $session->get('loginFailureTimestamp', new \DateTime('now'));

            if (!$session->has('loginFailureTimestamp')) {
                $session->set('loginFailureTimestamp', $tmp);
            }

            /** @var \DateTime $timestamp */
            $timestamp = clone $tmp;
            $timestamp->add(new \DateInterval('PT5M'));
            if (($interval = $timestamp->getTimestamp() - time()) > 0) {
                throw new ServiceUnavailableHttpException($interval);
            } else {
                $session->remove('loginFailure');
                $session->remove('loginFailureTimestamp');
            }
        }

        return $this->render('security/login.html.twig', ['username' => $authenticationUtils->getLastUsername(), 'error' => $authenticationUtils->getLastAuthenticationError()]);
    }

    #[Route('/forgotPassword', name: 'forgot_password')]
    public function forgotPasswordAction(Request $request, MailerInterface $mailer): Response
    {
        $forgotPassword = $this->createForm(\App\Form\Type\ForgotPasswordType::class);
        $forgotPassword->handleRequest($request);

        if ($forgotPassword->isSubmitted() && $forgotPassword->isValid()) {
            $data = $forgotPassword->getData();
            $em = $this->managerRegistry->getManager();
            $customer = $em->getRepository(\App\Entity\Customer::class)->findOneBy(['email' => $data['email']]);
            if ($customer) {
                if (($token = $em->getRepository(PasswordToken::class)->findOneBy(['customer' => $customer])) === null) {
                    $token = PasswordToken::generate($customer);
                    $em->persist($token);
                    $em->flush();
                }

                $email = $this->render('email/forgotPassword.html.twig', ['token' => $token->getToken()]);
                $message = (new Email())
                    ->subject('Reset Password')
                    ->html($email->getContent())
                    ->from(new Address($this->getParameter('order_email'), $this->getParameter('site.name')))
                    ->replyTo(new \Symfony\Component\Mime\Address($this->getParameter('contact_email'), $this->getParameter('site.name')))
                    ->to(new Address($customer->getEmail(), $customer->getName()));
                $mailer->send($message);
            }
        }

        return $this->render('security/forgotPassword.html.twig', ['form' => $forgotPassword->createView(), 'submitted' => ($forgotPassword->isSubmitted() && $forgotPassword->isValid())]);
    }

    #[Route('/resetPassword/{token}', name: 'reset_password')]
    public function resetPassword(Request $request, string $token, PasswordHasherFactoryInterface $passwordHasherFactory): Response|RedirectResponse
    {
        $em = $this->managerRegistry->getManager();
        $token = $em->getRepository(PasswordToken::class)->findOneBy(['token' => $token]);

        if (!$token) {
            return $this->redirectToRoute('login');
        }

        $customer = $token->getCustomer();
        $passwordResetForm = $this->createForm(
            \App\Form\Type\PasswordResetType::class,
            $customer,
            ['current_password' => false]
        );
        $passwordResetForm->handleRequest($request);

        if ($passwordResetForm->isSubmitted() && $passwordResetForm->isValid()) {
            $password = $passwordHasherFactory->getPasswordHasher($customer)->hash($customer->getPlainPassword());
            $customer->setPassword($password);
            $em->merge($customer);
            $em->remove($token);
            $em->flush();
            $this->addFlash('notice', 'Your password was changed successfully');
            return $this->redirectToRoute('login');
        }

        return $this->render('security/resetPassword.html.twig', ['form' => $passwordResetForm->createView(), 'token' => $token->getToken()]);
    }
}
