<?php
namespace App\Handler;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class AuthenticationHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $redirect = $request->headers->get('referer', $this->router->generate('login'));
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        // Redirect to the referer, assuming it will handle the login form
        return new RedirectResponse($redirect);
    }
}
