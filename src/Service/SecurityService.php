<?php
namespace App\Service;

use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\Customer;
use Doctrine\ORM\EntityManager;

class SecurityService
{
    public function __construct(private readonly Session $session, private readonly EntityManager $em)
    {
    }

    public function increaseFailureCount(AuthenticationFailureEvent $event)
    {
        $timestamp = $this->session->get('loginFailureTimestamp', new \DateTime('now', new \DateTimeZone('UTC')));
        /** @var \DateTime $tmp */
        $tmp = clone $timestamp;
        $tmp->add(new \DateInterval('PT1M'));
        if ($tmp->getTimestamp() > time()) {
            $count = $this->session->get('loginFailure', 0);
            $this->session->set('loginFailure', ++$count);
            $this->session->set('loginFailureTimestamp', $timestamp);
        } else {
            $this->session->remove('loginFailure');
            $this->session->remove('loginFailureTimestamp');
        }
    }

    public function increaseLoginCount(InteractiveLoginEvent $event)
    {
        $this->session->remove('loginFailure');
        $this->session->remove('loginFailureTimestamp');
        /** @var App\Entity\Customer $customer */
        $user = $event->getAuthenticationToken()->getUser();
        $user->setLoginCount($user->getLoginCount()+1);
        $user->setLastLogin(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->em->merge($user);
        $this->em->flush($user);
    }
}
