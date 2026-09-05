---
name: senior-it-recruiter
description: How to structure, write, and format a CV, cover letter, or Upwork proposal the way a senior technical recruiter would — correct document structure, ATS visibility, content that actually reads as strong, and stripping the "sounds like AI wrote it" tells that get a resume screened out. Use whenever composing or reviewing a CV, cover letter, or application draft, before it goes out.
---

# Senior IT Recruiter

This is general craft knowledge — not personal data. `skills/cv-tailoring/`
uses this skill for the CV itself; the structure/writing rules apply to
cover letters and Upwork proposals too, adapted to their own format.

## 1. How a recruiter actually reads a CV

Design the document for this reading pattern, don't fight it:

1. **~6-second scan**: job titles, company names, dates, top-line skills.
   Decides "keep reading or discard." If titles/dates aren't instantly
   scannable (consistent formatting, bold, aligned), the CV can die here
   even with great content underneath.
2. **~30-second read**: Summary + the most recent 1-2 roles' bullets.
   Decides "worth a real look." This is why the top third of page one
   carries the most weight in the whole document.
3. **Full read**: only if the first two passes survived. Older roles,
   full skills list, education.

Everything below exists to serve these three passes, in this order.

## 2. Document structure

**Section order** (senior IC, no employment gaps — this candidate's
case): Contact → Professional Summary → Skills → Experience (reverse
chronological) → Education → Languages. Skills before Experience for a
technical IC — a recruiter scanning for stack-match wants it before
committing to reading the whole experience section.

**Reverse chronological, always**, for a candidate with steady
progression and no gaps. Any other format (functional/skills-based,
hiding dates) reads as "hiding something" to an experienced recruiter —
don't use it unless there's an actual gap or pivot to manage, and even
then prefer a one-line honest note over restructuring the whole document.

**Length**: 7 years experience → 2 pages is correct, not a compromise.
1 page would force cutting real, relevant material; 3+ pages dilutes
signal and nobody reads that far on a first pass. Don't pad to fill 2
pages and don't fight to cram into 1.

**Recency-weighted detail** — this is a general principle, not just this
candidate's specific rule: the most recent 1-2 roles get full bullet
detail (4-6 bullets, quantified); older roles compress to 2-3 lines each,
context and tech stack only, no deep achievement narrative. A 5-year-old
role doesn't need the same bullet density as last month's work — nobody
reads it that closely, and over-detailing it just pushes the important
material further down the page.

**What to leave out entirely**:
- "References available upon request" — dead convention, never include.
- Full street address — city/country is enough, if that.
- An objective statement ("Seeking a challenging role where...") — cut
  straight to the summary's actual content instead.
- Hobbies/interests, unless one is genuinely relevant to the role (rare).
- A skills list padded with things not relevant to the target vacancy —
  dilutes what actually matters for this application.

**Format consistency**: one date format used throughout (e.g. "Apr 2024 -
Present", not mixed with "04/2024-present" elsewhere), one bullet
punctuation style throughout, one tense convention (past tense for past
roles is safest; present tense is fine for the current role's ongoing
responsibilities if used consistently). Body text 11-12pt, clear visual
hierarchy between company/title/dates and the bullets under them.

## 3. Writing a strong bullet

Formula: **action verb + what was actually done + the specific
tool/method + the quantified result**, in that rough order. Not every
bullet has all four (not everything has a real number), but the more of
them present, the stronger the line.

- Weak: "Responsible for backend reliability."
- Strong: "Fixed a batch-scale double-publishing bug with idempotent,
  content-hash slug creation that stays stable under parallel
  sub-agents."

**Sequencing within a role**: the *first* bullet under a role is the one
most relevant to the vacancy being applied to — not chronological order
of when it happened, relevance order for the reader in front of you right
now. This is the core of what "tailoring" actually means beyond just
swapping which bullets are included — reordering existing ones by
relevance is itself a form of tailoring, and it's free (no new content,
no risk).

**Impact over duties, specificity over vague competence claims,
quantify wherever a real number exists** — see also §5's tells table for
what happens when these are faked instead of real.

## 4. Visible — getting past the ATS filter

- No multi-column layouts, no content inside tables, no text embedded in
  images or icons used as bullet markers — plain, machine-readable
  structure only.
- Standard section headers a parser recognizes ("Professional Summary",
  "Experience", "Skills", "Education") — don't get creative with names.
- No critical information in a header/footer — some parsers drop those
  entirely.
- **Keyword strategy**: pull exact terms from the vacancy's
  "requirements"/"must-have" section first, mirror the exact phrasing
  ("REST API" not "APIs", the exact tool name they use). Prioritize
  must-have keywords over nice-to-have when space is tight. Keywords
  belong woven into real bullets and a real skills list — not piled into
  a stuffed tag cloud (that's a tell, see §5).

## 5. Invisible — stripping the "an LLM wrote this" tells

Same problem the `anti-slop` skill solves for prose in general — resumes
get the CV-specific version of the same tells, and recruiters
increasingly screen for them directly. **If `anti-slop` is available in
the session, run its full Gate 2 checklist and rewrite protocol on the
composed summary and every bullet before finalizing.** If it isn't,
apply this condensed, CV-specific version:

| Tell | CV-specific example | Fix |
|---|---|---|
| Buzzword soup, no grounding | "Highly motivated, results-driven professional leveraging cutting-edge solutions to drive business value" | Cut it. Say what he actually builds — see §3. |
| Uniform bullet rhythm | Every bullet: "Designed X. Implemented Y. Optimized Z." — identical grammatical shape, identical length, line after line | Vary structure and length the way a real person writes. |
| Unsourced round-number stats | "Improved performance by 200%" with nothing behind it | Ground it in something real ("hundreds of thousands of postbacks/day") or cut the number. |
| Keyword-stuffed skills line | A pipe-separated wall: "PHP \| Laravel \| AWS \| Docker \| K8s \| AI \| ML \| GenAI \| RAG" | Group into real categories, list only what's relevant to this vacancy (§4). |
| Generic filler adjectives | "expert," "passionate," "seamless," "robust," "cutting-edge" with nothing concrete attached | Delete or replace with the specific fact that would justify it. |
| Escalating triads for cadence | "Faster. More reliable. More scalable." as a rhythm device, not real content | Say the one true thing instead of three vague ones. |

**Self-check before finalizing**: read the summary and one experience
block out loud. Would a senior engineer say it this way to a respected
peer, or does it sound like a LinkedIn coach / a template? If the latter,
it needs another pass.
