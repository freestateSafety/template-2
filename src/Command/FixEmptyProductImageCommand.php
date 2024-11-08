<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixEmptyProductImageCommand extends Command
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:fix-empty-product-image')
            ->setDescription('Updates empty product images')
            ->setHelp('Run this to populate any empty image fields in the product database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->managerRegistry->getManager();
        $products = $em->getRepository(Product::class)->findBy(['image' => '']);
        /** @var Product $product */
        foreach ($products as $product) {
            $image = str_replace('.', '-', $product->getItemNumber());

            if (str_starts_with($image, 'SS')) {
                $tmp = explode('-', $product->getItemNumber());
                $image = $tmp[0];
            }

            if (strtolower($image) === 'haztab') {
                $image = strtoupper($image);
            }

            $product->setImage($image.'.gif');
            $em->merge($product);
        }

        $em->flush();
        $output->writeln('Successfully updated '.(is_countable($products) ? count($products) : 0).' product records');

        return 0;
    }
}
