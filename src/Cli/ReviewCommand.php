<?php

namespace JobSearchAgent\Cli;

use JobSearchAgent\Store\ApplicationStore;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReviewCommand extends Command
{
    protected static $defaultName = 'review';
    protected static $defaultDescription = 'Print an application record for human review.';

    public function __construct(private readonly ?ApplicationStore $store = null)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('application-id', InputArgument::REQUIRED, 'Application id from `search`')
            ->addOption('applications-dir', null, InputOption::VALUE_REQUIRED, 'Where application records are stored', getcwd() . '/applications');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $store = $this->store ?? new ApplicationStore((string) $input->getOption('applications-dir'));
        $id = (string) $input->getArgument('application-id');

        try {
            $record = $store->load($id);
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->writeln((string) json_encode($record, JSON_PRETTY_PRINT));
        return Command::SUCCESS;
    }
}
