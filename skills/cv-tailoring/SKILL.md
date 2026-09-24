---
name: cv-tailoring
description: Compose a CV tailored to a specific job vacancy from the candidate's real experience. Use when applying to a job vacancy and a tailored CV is needed for it.
---

# CV Tailoring

You are acting as a senior technical recruiter preparing this candidate's
CV for one specific vacancy. Your job is not to pick between pre-made CV
versions — it's to compose a new one, every time, from source material.

## Inputs you need before starting

1. The vacancy's job description (title, requirements, tech stack, what
   they emphasize).
2. Access to `profile/` (candidate facts, skills, preferences) and this
   skill's `reference/` directory (process rules + achievement pool).
3. The `senior-it-recruiter` skill — invoke/read it before step 5. It's
   where the general "how to build it well" craft lives (document
   structure, section order, bullet formula/sequencing, ATS visibility,
   stripping AI-sounding tells); this skill only covers this specific
   candidate's data and process.

## Process

1. Read the vacancy description. Identify: the core tech stack, the
   seniority/scope signal, and which of the candidate's two "angles" it
   maps to — AI-agent engineering, plain backend/Laravel, high-load/
   performance, or DevOps/infra (a vacancy can mix more than one).
2. Read `profile/experience.md`'s two most recent role entries (current
   role, and the role immediately before it) — pull the raw facts
   relevant to what you identified in step 1. These are the only two
   roles you tailor; everything else in that file is fixed (step 4).
3. Read `profile/skills.md` — select and order the skills this vacancy
   actually cares about. If the vacancy names something `profile/`
   doesn't confirm, follow `reference/recruiter-playbook.md`'s
   "Honesty / experience-gap rule" (ask if it matters, omit if it
   doesn't, never present it as confirmed) — and never present
   anything from `profile/skills.md`'s "currently learning" section as
   existing production experience.
4. Use `profile/experience.md`'s Contact section, its older/fixed role
   entries, Education, and Languages as fixed: the facts may not be
   altered or invented, but shortening and reordering bullets for
   relevance is allowed and expected.
5. Invoke the `senior-it-recruiter` skill, then compose the Professional
   Summary (2-3 sentences, vacancy-specific angle) and the two tailored
   roles' experience bullets (drawn from step 2's raw material)
   following its visibility/strength/de-AI-ification rules.
6. Follow every rule in `reference/recruiter-playbook.md`, including
   the "Honesty / experience-gap rule" and which roles are tailored vs.
   fixed.
7. Compose a cover letter (150-200 words) from the same tailored
   material, following `senior-it-recruiter`'s writing rules. **Never
   name a past/current employer in it** (see
   `reference/recruiter-playbook.md`'s "Cover letters: no employer
   names" rule) — talk about the experience and what was built, not
   which company it happened at. The CV's Experience section still
   names employers normally; this restriction is cover-letter-only.
8. Output the composed CV and cover letter in the structure below, plus
   a separate log listing any open questions asked, any gap disclosed
   to the recruiter vs. only tracked internally, and interview prep
   notes (empty/"None" if there's nothing to log).

## Hard rules

- Never invent or alter a fact in `profile/experience.md`'s fixed
  sections (Contact, the older/fixed role entries, Education,
  Languages) — reorder or shorten only.
- Never invent a specific, checkable claim about work that didn't happen
  (a project, an employer, a metric). Extending a real skill to a
  plausible adjacent one is a separate thing, governed entirely by
  `reference/recruiter-playbook.md`'s "Honesty / experience-gap rule" —
  fabricating history is never covered by it.
- Do not reveal the NDA'd prior role's product specifics, regardless of
  what the vacancy asks about — see `profile/experience.md` for which
  role that is and its exact constraint.
- **Facts come only from `profile/` (either `experience.md` or
  `skills.md` — a fact can be confirmed in either),
  `reference/key-achievements-pool.md`, and this session's explicit
  confirmations from the user — never from a previously generated CV,
  cover letter, or `stretch-log.md`.** A prior application's wording is
  a style/structure
  example, not a source of truth. See `reference/recruiter-playbook.md`'s
  "Honesty / experience-gap rule" for what to do when `profile/` doesn't
  confirm something the vacancy names — the short version: ask if it
  matters, omit if it doesn't, describe real-but-limited experience
  precisely, never present it as fully confirmed just because an
  earlier application's text said so.

## Output format

```
## CV
[Contact]
[Professional Summary]
[Skills]
[Experience: two most recent roles (tailored) + older roles (fixed)]
[Education]
[Languages]

## Cover Letter
[150-200 words, no employer names]

## Gap / question log
- [open question asked to the user, and the answer] — or
- [gap noted: disclosed to the recruiter in the cover letter/a form
  answer, or tracked internally only] — [what to study/prep before an
  interview]
(or: "None — nothing unconfirmed came up.")
```

The CV portion here is the agent's structured output. Fill it into
`templates/cv-template.html` (copy it into the application's own
`cv.html` — see that file's header comment for the placeholder rules,
including dropping the contact line entirely for Upwork), then run
`scripts/export-cv.sh applications/<id>/cv.html` to produce
`cv.pdf`. This skill does not run that export step itself.
