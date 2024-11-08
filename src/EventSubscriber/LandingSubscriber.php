<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class LandingSubscriber
{
    public function __construct(private readonly ContainerInterface $container, private readonly TokenStorage $storage)
    {
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (($landing_page = $this->container->getParameter('site.landing')) !== null) {
            $user = ($this->storage->getToken())? $this->storage->getToken()->getUser() : null;
            if (!is_a($user, 'App\Entity\Customer') && $event->getRequest()->attributes->get('_controller') !== 'App\Controller\SecurityController::loginAction') {
                $event->setResponse(new RedirectResponse($landing_page));
                return;
            }
        }
    }
}