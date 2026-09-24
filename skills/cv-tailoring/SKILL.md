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
   actually cares about. Apply the honesty/stretch rule in
   `reference/recruiter-playbook.md` if the vacancy names something not
   yet listed there as "already true," and never present anything from
   `profile/skills.md`'s "currently learning" section as existing
   production experience.
4. Use `profile/experience.md`'s Contact section, its older/fixed role
   entries, Education, and Languages as fixed: the facts may not be
   altered or invented, but shortening and reordering bullets for
   relevance is allowed and expected.
5. Invoke the `senior-it-recruiter` skill, then compose the Professional
   Summary (2-3 sentences, vacancy-specific angle) and the two tailored
   roles' experience bullets (drawn from step 2's raw material)
   following its visibility/strength/de-AI-ification rules.
6. Follow every rule in `reference/recruiter-playbook.md` (stretch-
   logging, which roles are tailored vs. fixed).
7. Compose a cover letter (150-200 words) from the same tailored
   material, following `senior-it-recruiter`'s writing rules. **Never
   name a past/current employer in it** (see
   `reference/recruiter-playbook.md`'s "Cover letters: no employer
   names" rule) — talk about the experience and what was built, not
   which company it happened at. The CV's Experience section still
   names employers normally; this restriction is cover-letter-only.
8. Output the composed CV and cover letter in the structure below, plus
   a separate "stretch log" listing anything added that wasn't already
   true before this application (empty list if nothing was stretched).

## Hard rules

- Never invent or alter a fact in `profile/experience.md`'s fixed
  sections (Contact, the older/fixed role entries, Education,
  Languages) — reorder or shorten only.
- Never invent a specific, checkable claim about work that didn't happen
  (a project, an employer, a metric) — stretching is about plausible
  skill/technology extension, not fabricated history.
- Every stretch MUST appear in the stretch log. No silent stretches.
- Do not reveal the NDA'd prior role's product specifics, regardless of
  what the vacancy asks about — see `profile/experience.md` for which
  role that is and its exact constraint.

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

## Stretch log
- [skill/claim added] — [why it's a plausible extension] — [what to
  study/prep before an interview]
(or: "None — everything in this CV was already true.")
```

The CV portion here is the agent's structured output. Fill it into
`templates/cv-template.html` (copy it into the application's own
`cv.html` — see that file's header comment for the placeholder rules,
including dropping the contact line entirely for Upwork), then run
`scripts/export-cv.sh applications/<id>/cv.html` to produce
`cv.pdf`. This skill does not run that export step itself.
