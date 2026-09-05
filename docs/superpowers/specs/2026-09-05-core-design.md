# job-search-agent — core design

Status: approved
Date: 2026-09-05

## Purpose

Build the portable core of the job-search agent: a tool-calling agent
loop plus a `JobSiteAdapter` abstraction, first wired to Upwork. This is
the second agent build (after `code-review-agent`), same architectural
lineage — a portable, testable loop behind a swappable adapter interface,
with a hard human-approval gate before anything is ever submitted.

Full product context, decisions, and constraints already recorded in the
Obsidian vault: `Projects/JobSearchAgent/_MOC.md`,
`Projects/JobSearchAgent/2026-09-03-brainstorming-open-questions.md`, and
this repo's own `soul.md`. This spec covers only the core build — not the
CV-tailoring skill content (already built) and not the full multi-platform
rollout (Upwork only, for now).

## Non-goals for this build

- No real Upwork OAuth2 wiring yet — no dev app registered on
  developers.upwork.com yet. `UpworkAdapter` is built against an
  injected HTTP-client interface and JSON fixtures; swapping in the real
  client later requires no changes to the adapter itself.
- No deep integration of `skills/cv-tailoring` / `skills/senior-it-recruiter`
  into the `prep` command yet — that command is scaffolded (accepts an
  application id, transitions state) but the actual tailoring-agent call
  is a fast-follow, not part of this spec.
- No Wellfound/DOU/Djinni adapters yet — interface must support them
  later without breaking changes, but only `UpworkAdapter` ships now.
- No prior-contact-check tool (Gmail + platform messaging) yet — the
  `ApplicationStore` schema reserves a field for it, but the check itself
  is not implemented in this pass.

## Architecture

### Repo

New git repo at `~/income-pipeline/job-search-agent` (local-only, not
public — contains personal CV/experience data). PHP 8.1+, Composer,
PSR-4 autoload `JobSearchAgent\` → `src/`. Mirrors `code-review-agent`'s
project layout (`bin/`, `src/`, `tests/`, `docs/superpowers/`).

### Core agent loop (`src/Agent/`, `src/Http/`)

Ported and adapted from `code-review-agent`'s `AgentLoop` /
`ToolInterface` / Anthropic HTTP client — same shape, generalized off
code-review specifics:

- `AnthropicClient` (`src/Http/`) — thin wrapper over the Messages API,
  supports `tool_use` / `tool_result` turns. Reads `ANTHROPIC_API_KEY`
  from `.env`.
- `ToolInterface` (`src/Tool/`) — `name(): string`, `schema(): array`
  (JSON schema for input), `execute(array $input): ToolResult`.
- `AgentLoop` (`src/Agent/`) — constructed with a system prompt + a list
  of `ToolInterface` instances; `run(string $userMessage): AgentResult`
  drives the tool-calling loop (call Claude → dispatch any `tool_use`
  blocks to matching tools → feed `tool_result`s back → repeat until a
  plain text stop) up to a max-turns guard (env-overridable, same
  pattern as `code-review-agent`'s `ANTHROPIC_MAX_TURNS` fix).
- `AgentResult` / `AgentLoopException` — same shape as the precedent.

This loop is domain-agnostic: it doesn't know about vacancies or CVs, only
tools and turns. Each CLI phase constructs its own system prompt +
toolset and hands it to the same `AgentLoop` class.

### `JobSiteAdapterInterface` (`src/Adapter/`)

```php
interface JobSiteAdapterInterface
{
    /** @return Vacancy[] */
    public function searchVacancies(SearchCriteria $criteria): array;

    public function getVacancyDetails(string $vacancyId): VacancyDetail;

    public function submitApplication(Application $application): SubmissionResult;
}
```

- `Vacancy` / `VacancyDetail` / `SearchCriteria` / `Application` /
  `SubmissionResult` are plain value objects (`src/Adapter/Value/`),
  shared across all future adapters.
- Each adapter also exposes its read operations (`searchVacancies`,
  `getVacancyDetails`) as `ToolInterface` wrappers (`src/Tool/`), so the
  `search` phase's `AgentLoop` can call them. **`submitApplication` is
  deliberately never wrapped as a tool** — see Human-approval gate below.

### `UpworkAdapter` (`src/Adapter/Upwork/`)

First implementation. Talks to an injected `UpworkApiClientInterface`
rather than a concrete HTTP client directly:

```php
interface UpworkApiClientInterface
{
    public function searchJobs(array $params): array; // raw decoded JSON
    public function getJob(string $id): array;
    public function submitProposal(string $jobId, array $payload): array;
}
```

- `FixtureUpworkApiClient` (`tests/fixtures/upwork/*.json` + a small
  loader) — the only implementation that exists in this pass. Returns
  canned search results / job details / a fake submit-acknowledgement,
  keyed off the request so tests can assert on different scenarios
  (empty results, one match, error response).
- `UpworkAdapter` itself only depends on `UpworkApiClientInterface` and
  maps raw JSON → the `Vacancy`/`VacancyDetail`/`SubmissionResult` value
  objects. When real credentials exist, a `HttpUpworkApiClient`
  (OAuth2 + Guzzle or PHP's curl, matching `code-review-agent`'s
  dependency-light style) implements the same interface — zero changes
  to `UpworkAdapter` or its tests.

### `ApplicationStore` (`src/Store/`)

One JSON file per application, at
`applications/<date>-<slug>/record.json` — continuing the folder
convention already used by the two manually-submitted test applications.

Schema:

```json
{
  "id": "2026-09-05-upwork-acme-backend",
  "platform": "upwork",
  "vacancy_id": "...",
  "status": "found",
  "cv_version": null,
  "stretches": [],
  "prior_contact_checked": false,
  "created_at": "2026-09-05T10:00:00+02:00",
  "updated_at": "2026-09-05T10:00:00+02:00"
}
```

`status` transitions: `found` → `drafted` → `submitted` (set only by the
`submit` command, never by the agent loop). `ApplicationStore` is a plain
read/write class (`create`, `load`, `save`, `list`) — no database, matches
the file-based style already used for `soul.md`/skills.

### Human-approval gate

This is an architectural guarantee, not a prompt instruction:

- The `AgentLoop` used by the `search` phase is only ever given
  *read* tools (`search_vacancies`, `get_vacancy_details`).
- `JobSiteAdapterInterface::submitApplication()` is called from exactly
  one place: the `submit` CLI command, gated behind an explicit `--yes`
  flag or an interactive confirm prompt. No `ToolInterface` wraps it, so
  no agent loop — now or in any future phase — can invoke it on its own.
- This directly enforces `soul.md`'s "nothing gets submitted without the
  human being able to review it first" at the code-structure level.

### CLI (`bin/job-agent`, Symfony Console — matches `code-review-agent`)

- `job-agent search --platform=upwork "backend php"` — builds an
  `AgentLoop` with the Upwork read-tools, runs it against the query,
  prints a shortlist, and writes one `ApplicationStore` record per
  vacancy the agent surfaces, `status=found`.
- `job-agent prep <application-id>` — scaffolded command: loads the
  record, transitions to `status=drafted`. Actual CV-tailoring-agent
  integration is a fast-follow (non-goal above).
- `job-agent review <application-id>` — prints the stored draft (once
  `prep` produces one) for the human to read.
- `job-agent submit <application-id> [--yes]` — the sole call site of
  `submitApplication()`; refuses to run without `--yes` or an
  interactive "y" confirm; sets `status=submitted`.

### Config

`.env` (gitignored): `ANTHROPIC_API_KEY` now; `UPWORK_CLIENT_ID` /
`UPWORK_CLIENT_SECRET` added later once the dev app exists. `.env.example`
checked in with placeholder keys, same as `code-review-agent`'s
`review-agent.json.example` pattern.

## Testing

- `AgentLoop` / `ToolInterface`: port `code-review-agent`'s existing
  test style (mock Anthropic HTTP responses, assert tool dispatch and
  loop termination).
- `UpworkAdapter`: tested entirely against `FixtureUpworkApiClient` —
  no network calls in the test suite.
- `ApplicationStore`: round-trip create/load/save against a temp
  directory.
- CLI commands: Symfony Console's `CommandTester`, same as
  `code-review-agent`'s `tests/Cli/`.

## Open questions carried forward (not blocking this build)

- Exact `prep` → tailoring-skill integration shape (how the CLI feeds
  `skills/cv-tailoring/` + `skills/senior-it-recruiter/` content into an
  `AgentLoop` call) — design when that command is built.
- Prior-contact-check tool (Gmail API + Upwork's internal messaging) —
  schema field reserved, implementation deferred.
- Whether `AgentLoop`/`ToolInterface` should become an actual shared
  Composer package between `code-review-agent` and `job-search-agent`,
  or stay duplicated-and-adapted. Deferred — only two call sites exist
  so far; revisit if a third agent needs the same core.
