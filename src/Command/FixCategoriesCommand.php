<?php

namespace App\Command;

use App\Entity\ProductCategory;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class FixCategoriesCommand extends Command
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:fix-categories')
            ->setDescription('Fixes reversed categories')
            ->setHelp(
                'Running this command will allow you to reverse the sub-categories of main categories by selecting '.
                'which two categories from a list.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->managerRegistry->getManager();
        $categoryRepository = $em->getRepository(ProductCategory::class);
        $categories = $categoryRepository->matching(Criteria::create()->where(Criteria::expr()->isNull('parent')));
        $catIds = [];
        $output->writeln('Please select two categories from below:');

        /** @var ProductCategory $category */
        foreach ($categories as $category) {
            $catIds[$category->getId()] = $category->getLabel();
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            '',
            $catIds
        );
        $question->setMultiselect(true);

        $reverse = $helper->ask($input, $output, $question);
        $output->writeln('Reversing sub-categories of '.implode(',', $reverse));
        [$first, $second] = array_values($categories->filter(fn($category) => in_array($category->getLabel(), $reverse))->toArray());

        $firstSubCats = $first->getSubCategories()->toArray();
        /** @var ProductCategory $category */
        foreach ($firstSubCats as $key => $category) {
            $category->setParent($second);
            $category->setPriority($key + 1);
            $em->merge($category);
        }

        $secondSubCats = $second->getSubCategories()->toArray();
        /** @var ProductCategory $category */
        foreach ($secondSubCats as $key => $category) {
            $category->setParent($first);
            $category->setPriority($key + 1);
            $em->merge($category);
        }

        $em->flush();

        return 0;
    }
}
