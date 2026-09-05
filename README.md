# job-search-agent

A job-search agent built around an Anthropic Claude tool-calling loop. It
searches job platforms, drafts and reviews applications, and submits them
— with a human in the loop at the one step that actually matters. This is
the second build in a small series (after `code-review-agent`) sharing the
same architecture: a portable, testable tool-calling core sitting behind
a swappable platform adapter interface.

## Commands

- `job-agent search --platform=upwork "backend php laravel"` — runs the
  agent loop with read-only search tools against the given platform,
  prints a shortlist of vacancies it thinks are worth pursuing, and
  writes one application record per shortlisted vacancy (status `found`).
- `job-agent prep <application-id>` — moves an application into the
  drafting stage (status `drafted`). Currently a scaffold: it transitions
  state but does not yet call into the CV-tailoring skill content under
  `skills/` — that integration is the next piece of work.
- `job-agent review <application-id>` — prints the stored application
  record so a human can read the current draft before deciding what
  happens next.
- `job-agent submit <application-id> [--yes]` — sends the application.
  Refuses to run without `--yes` or an interactive confirmation.

## The human-approval gate

This is the project's central design decision, enforced in the code
structure rather than left to a prompt: `submit` is the only code path
that is ever allowed to call a platform adapter's `submitApplication()`
method. The agent loop that powers `search` is only ever handed read
tools (search, get-details) — there is no tool wrapper around
`submitApplication()` anywhere in the codebase, so no agent loop, now or
in any future phase, can reach it. Nothing gets sent to a job platform
without a person deciding to send it.

## Current state

`UpworkAdapter` talks to an injected `UpworkApiClientInterface` rather
than an HTTP client directly. Right now the only implementation of that
interface is `FixtureUpworkApiClient`, which serves canned JSON from
`tests/fixtures/upwork/` — there's no registered Upwork developer app
yet, so no real API calls happen. Building a real
`UpworkApiClientInterface` HTTP client (OAuth2) is the natural next step,
and it requires zero changes to `UpworkAdapter` itself — the adapter
already only depends on the interface.

## Setup

```bash
composer install
cp .env.example .env
# fill in ANTHROPIC_API_KEY in .env
vendor/bin/phpunit
php bin/job-agent search "some query"
```
