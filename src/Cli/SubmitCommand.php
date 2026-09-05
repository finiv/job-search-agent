<?php

namespace JobSearchAgent\Cli;

use JobSearchAgent\Adapter\JobSiteAdapterInterface;
use JobSearchAgent\Adapter\Upwork\FixtureUpworkApiClient;
use JobSearchAgent\Adapter\Upwork\UpworkAdapter;
use JobSearchAgent\Adapter\Value\Application as ApplicationValue;
use JobSearchAgent\Store\ApplicationStore;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SubmitCommand extends Command
{
    protected static $defaultName = 'submit';
    protected static $defaultDescription = 'Submit an application. Requires explicit confirmation — the only place submission ever happens.';

    public function __construct(
        private readonly ?ApplicationStore $store = null,
        private readonly ?JobSiteAdapterInterface $adapter = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('application-id', InputArgument::REQUIRED, 'Application id from `search`')
            ->addOption('applications-dir', null, InputOption::VALUE_REQUIRED, 'Where application records are stored', getcwd() . '/applications')
            ->addOption('fixtures-dir', null, InputOption::VALUE_REQUIRED, 'Upwork fixtures directory (until real API creds exist)', dirname(__DIR__, 2) . '/tests/fixtures/upwork')
            ->addOption('yes', null, InputOption::VALUE_NONE, 'Confirm submission non-interactively');
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

        if ($record['status'] === 'submitted') {
            $io->warning("Application {$id} was already submitted.");
            return Command::SUCCESS;
        }

        $confirmed = (bool) $input->getOption('yes');
        if (!$confirmed) {
            $confirmed = $io->confirm(
                "Submit application {$id} ({$record['vacancy_id']}) now? This cannot be undone.",
                false
            );
        }

        if (!$confirmed) {
            $io->writeln('Cancelled — nothing submitted.');
            return Command::SUCCESS;
        }

        $adapter = $this->adapter ?? new UpworkAdapter(
            new FixtureUpworkApiClient((string) $input->getOption('fixtures-dir'))
        );

        $result = $adapter->submitApplication(new ApplicationValue(
            vacancyId: (string) $record['vacancy_id'],
            coverLetter: (string) ($record['cover_letter'] ?? ''),
        ));

        if (!$result->success) {
            $io->error("Submission failed: {$result->message}");
            return Command::FAILURE;
        }

        $record['status'] = 'submitted';
        $store->save($record);

        $io->success("Submitted application {$id}. {$result->message}");
        return Command::SUCCESS;
    }
}
