<?php
namespace App\Controller;
use Doctrine\Persistence\ManagerRegistry;

abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
	public function __construct(protected readonly ManagerRegistry $managerRegistry)
    {
	}
}
