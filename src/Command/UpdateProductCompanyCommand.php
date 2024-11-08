<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateProductCompanyCommand extends Command
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:update-product-company')
            ->setDescription('Update the company name in product names')
            ->addArgument('old_company', InputArgument::REQUIRED, 'Previous company name')
            ->addArgument('new_company', InputArgument::REQUIRED, 'New company name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $old_company = $input->getArgument('old_company');
        $new_company = $input->getArgument('new_company');

        $em = $this->managerRegistry->getManager();
        $productRepository = $em->getRepository(Product::class);
        $products = $productRepository->matching(Criteria::create()->where(Criteria::expr()->contains('notes', $old_company)));

        /** @var Product $product */
        foreach ($products as $product) {
            $product->setNotes(str_ireplace((string) $old_company, (string) $new_company, $product->getNotes()));
            $em->merge($product);
        }

        $em->flush();
        $output->writeln('Updated a total of '.$products->count().' products');

        return 0;
    }
}
