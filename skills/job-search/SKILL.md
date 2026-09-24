---
name: job-search
description: Master workflow for finding, evaluating, and applying to a vacancy for this candidate — the single entry point that sequences profile, cv-tailoring, senior-it-recruiter, submission, and record-keeping in the right order. Use this first for any session that searches for, evaluates, drafts, or submits a job application.
---

# Job Search — master workflow

Read `soul.md` and `profile/` before anything else. This skill exists
so the pipeline below runs the same way regardless of which files a
session happens to open first.

## 1. Read the profile
`profile/experience.md`, `profile/skills.md`, `profile/preferences.md`.

If this session edits `soul.md`, any file in `profile/`, or
`skills/cv-tailoring/reference/`, update the private backup before
finishing — the location is `$PRIVATE_BACKUP_VAULT_PATH/private-backup/`
(read that env var from `.env`; see `.env.example`, never hardcode the
actual path in a committed file). That folder's own `README.md` has
the exact copy commands.

If this session creates or changes any `applications/*/record.json`,
regenerate the lightweight Obsidian tracker before finishing:

```bash
python3 scripts/export-applications-tracker.py
```

## 2. Search and evaluate
For each candidate vacancy, label it one of: **strong match**,
**adjacent stack**, or **career stretch** — see `profile/preferences.md`'s
role/stack-fit stance. A framework gap or unfamiliar tool is not by
itself a reason to skip; missing salary/conditions is a reason to ask
the user, not to silently reject.

**Signal extraction, not company research by default.** Read the
vacancy text for concrete product signals — what the system actually
does — and map them to specific real skills, the way "the product has
streaming/event data → mention RabbitMQ" works. This is about being
precise with what's already in front of you, not about researching the
company. Do not add a separate company-research step by default: at
current application volume, a real per-company research pass (site,
blog, GitHub, funding, stack) is too slow and too expensive to justify
before there's even a reply. The one exception: if the vacancy text
itself is too thin to extract any real signal from, a quick,
time-boxed look at the company's own site (a couple of minutes, not a
research pass) is fine — never a default step for every application.

## 3. Prior-contact check
Check Gmail and the platform's own messaging. Record as `checked`,
`not_checked`, or `unavailable` — see `reference/recruiter-playbook.md`.

## 4. Prepare the package
Invoke `cv-tailoring`, then `senior-it-recruiter`. Select the subset of
`profile/skills.md` that the vacancy/product signal actually supports —
**omit what isn't relevant, don't just reorder everything to the
bottom of the page.** A CV that lists every skill regardless of fit is
exactly what this step exists to avoid.

## 5. Export and check the PDF
Fill `templates/cv-template.html` into the application's `cv.html`,
then run `scripts/export-cv.sh applications/<id>/cv.html`. Before
attaching the result, check: no orphaned heading at a page break,
contact block correct for the platform, text actually extractable
(not flattened to an image), and a final visual read of the PDF
itself.

## 6. Submit only after explicit per-vacancy approval
No standing auto-submit mode, ever — see `soul.md`.

## 7. Record the outcome
Update `record.json` with `"schema_version": 2` and: `status`,
`prior_contact_checked`, `attached_cv_file`/`attached_cv_verified`,
`next_action`/`next_action_at`, `last_response_at`. Then run
`scripts/validate-application.py applications/<id>/record.json` before
treating the record as done. Applications created before this schema
existed are legacy (no `schema_version`) — the validator skips them
rather than failing; don't mass-convert them, see the plan's Task 6/7.

## Pace
No fixed daily cap. Watch each platform's own signal instead of a
global limit — its cost-per-application (e.g. Upwork Connects) and any
block/rejection signal — and slow down on the specific platform that
shows one, not globally.
