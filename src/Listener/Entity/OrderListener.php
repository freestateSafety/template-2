<?php
namespace App\Listener\Entity;


use App\Entity\Order;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class OrderListener
{
    public function preUpdate(Order $order, PreUpdateEventArgs $args)
    {
        if (($args->hasChangedField('deleted') || $args->hasChangedField('fulfilled')) && $order->getId() !== null) {
            $order->setUpdated(new \DateTime('now'));
        }
    }
}