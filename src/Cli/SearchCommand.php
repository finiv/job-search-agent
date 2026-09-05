<?php

namespace JobSearchAgent\Cli;

use JobSearchAgent\Adapter\JobSiteAdapterInterface;
use JobSearchAgent\Adapter\Upwork\FixtureUpworkApiClient;
use JobSearchAgent\Adapter\Upwork\UpworkAdapter;
use JobSearchAgent\Agent\AgentLoop;
use JobSearchAgent\Agent\AgentLoopException;
use JobSearchAgent\Http\AnthropicApiException;
use JobSearchAgent\Http\AnthropicClientInterface;
use JobSearchAgent\Http\AnthropicHttpClient;
use JobSearchAgent\Store\ApplicationStore;
use JobSearchAgent\Tool\GetVacancyDetailsTool;
use JobSearchAgent\Tool\SearchVacanciesTool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SearchCommand extends Command
{
    protected static $defaultName = 'search';
    protected static $defaultDescription = 'Search vacancies on a job platform and let the agent shortlist them.';

    private const SYSTEM_PROMPT = <<<'PROMPT'
You are a job-search assistant helping a senior backend engineer find
worthwhile vacancies. Use search_vacancies to find candidates for the
given query, then use get_vacancy_details on anything that looks
promising before deciding — do not shortlist from the snippet alone.

When you are done, respond with ONLY a single JSON object (no prose, no
markdown fences) matching exactly this shape:
{"shortlist": [{"vacancy_id": "...", "title": "...", "reason": "one sentence on why this is a good fit"}]}

If nothing is worth shortlisting, return {"shortlist": []}.
PROMPT;

    public function __construct(
        private readonly ?AnthropicClientInterface $client = null,
        private readonly ?JobSiteAdapterInterface $adapter = null,
        private readonly ?ApplicationStore $store = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('query', InputArgument::REQUIRED, 'Free-text search query')
            ->addOption('platform', null, InputOption::VALUE_REQUIRED, 'Job platform to search', 'upwork')
            ->addOption('applications-dir', null, InputOption::VALUE_REQUIRED, 'Where to store application records', getcwd() . '/applications')
            ->addOption('fixtures-dir', null, InputOption::VALUE_REQUIRED, 'Upwork fixtures directory (until real API creds exist)', dirname(__DIR__, 2) . '/tests/fixtures/upwork');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $platform = (string) $input->getOption('platform');

        if ($platform !== 'upwork') {
            $io->error("Unsupported platform: {$platform}");
            return Command::FAILURE;
        }

        $adapter = $this->adapter ?? new UpworkAdapter(
            new FixtureUpworkApiClient((string) $input->getOption('fixtures-dir'))
        );

        $client = $this->client;
        if ($client === null) {
            $apiKey = getenv('ANTHROPIC_API_KEY') ?: '';
            if ($apiKey === '') {
                $io->error('ANTHROPIC_API_KEY environment variable is not set.');
                return Command::FAILURE;
            }
            $client = new AnthropicHttpClient($apiKey);
        }

        $store = $this->store ?? new ApplicationStore((string) $input->getOption('applications-dir'));

        $maxTurns = getenv('ANTHROPIC_MAX_TURNS');
        $maxTurns = ($maxTurns !== false && ctype_digit($maxTurns) && (int) $maxTurns > 0) ? (int) $maxTurns : 15;

        $agent = new AgentLoop(
            client: $client,
            systemPrompt: self::SYSTEM_PROMPT,
            tools: [
                new SearchVacanciesTool($adapter),
                new GetVacancyDetailsTool($adapter),
            ],
            model: getenv('ANTHROPIC_MODEL') ?: 'claude-sonnet-5',
            maxTurns: $maxTurns,
        );

        try {
            $result = $agent->run('Search for vacancies matching: ' . (string) $input->getArgument('query'));
        } catch (AnthropicApiException|AgentLoopException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $decoded = json_decode($result->text, true);
        $shortlist = is_array($decoded) ? ($decoded['shortlist'] ?? []) : [];

        if ($shortlist === []) {
            $io->success('No vacancies shortlisted.');
            return Command::SUCCESS;
        }

        foreach ($shortlist as $entry) {
            $vacancyId = (string) ($entry['vacancy_id'] ?? '');
            $title = (string) ($entry['title'] ?? $vacancyId);
            $reason = (string) ($entry['reason'] ?? '');

            $record = $store->create($platform, $vacancyId, $title);
            $io->writeln("[{$record['id']}] {$title} — {$reason}");
        }

        return Command::SUCCESS;
    }
}
