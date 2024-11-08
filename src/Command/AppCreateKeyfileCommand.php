<?php

namespace App\Command;

use Defuse\Crypto\Key;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AppCreateKeyfileCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('app:create-keyfile')
            ->setDescription('Generate the secret keyfile for encryption.')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $argument = $input->getArgument('argument');

        if ($input->getOption('option')) {
            // ...
        }

        $keyfile = $this->getContainer()->getParameter('keyfile');
        if (!\file_exists($keyfile)) {
            /** @var Key $key */
            $key = Key::createNewRandomKey();
            file_put_contents($keyfile, $key->saveToAsciiSafeString());
            chmod($keyfile, 0400);
            $output->writeln('Created keyfile '.$keyfile);
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');
            $paymentRepository = $em->getRepository('App:Payment');
            $payments = $paymentRepository->findAll();
            foreach ($payments as $payment) {
                $payment->setDetails($payment->getDetails(false), $key);
                $em->merge($payment);
            }
            $em->flush();
            $output->writeln('Updated '.(is_countable($payments) ? count($payments) : 0).' payment records with encryption');
        }

        return 0;
    }

}
