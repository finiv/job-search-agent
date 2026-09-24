#!/usr/bin/env python3
"""Validate an application record.json against the v2 schema.

Usage:
    scripts/validate-application.py applications/<id>/record.json [more paths...]
    scripts/validate-application.py --all   # check every record under applications/

Schema (workflow-refactor plan, Task 5): a v2 record carries
"schema_version": 2 and a single `status` field covering the whole
lifecycle (found -> drafted -> submitted -> replied ->
interview_scheduled -> offer/rejected/withdrawn, or blocked). There is
no separate `submitted` boolean.

Legacy records (no "schema_version": 2 — the whole current
applications/ archive, as of this script's introduction) are reported
as legacy and skipped, not failed. This script does not migrate them —
see the plan's Task 6 (trial run) and Task 7 (reconciliation) for that.
Not PHP on purpose: this is workflow tooling, kept separate from the
unrelated src/ portfolio prototype (see CLAUDE.md).
"""
from __future__ import annotations

import json
import sys
from datetime import datetime
from pathlib import Path

VALID_STATUSES = {
    "found", "drafted", "submitted", "blocked",
    "replied", "interview_scheduled", "offer", "rejected", "withdrawn",
}
VALID_CONTACT_STATES = {"checked", "not_checked", "unavailable"}
VALID_VERIFIED_STATES = {"yes", "no", "unknown"}
# Statuses that mean an application has actually gone out the door —
# the point past which "what did we attach?" needs a real answer.
SENT_STATUSES = {"submitted", "replied", "interview_scheduled", "offer", "rejected", "withdrawn"}
REQUIRED_FIELDS = [
    "id", "platform", "company", "title", "url",
    "status", "prior_contact_checked", "created_at", "updated_at",
]
DATE_FIELDS = ["created_at", "updated_at", "submitted_at", "next_action_at", "last_response_at"]
# Statuses that plausibly need a tracked next step -- silently letting
# one of these sit with no next_action and no next_action_at is exactly
# how a live conversation goes stale unnoticed.
STATUSES_NEEDING_NEXT_ACTION = {"interview_scheduled"}


def is_valid_iso_date(value: str) -> bool:
    try:
        datetime.fromisoformat(value)
        return True
    except (TypeError, ValueError):
        return False


def validate(record: dict, path: str) -> list[str]:
    schema_version = record.get("schema_version")
    if schema_version is None:
        return [f"LEGACY {path}"]
    if schema_version != 2:
        return [f"{path}: unsupported schema_version {schema_version!r} -- this validator only knows schema v2; a record from a newer/different schema must not be silently treated as legacy"]

    errors: list[str] = []

    for field in REQUIRED_FIELDS:
        if field not in record or record[field] in (None, ""):
            errors.append(f"{path}: missing required field '{field}'")

    status = record.get("status")
    if status is not None and status not in VALID_STATUSES:
        errors.append(f"{path}: invalid status '{status}' (expected one of {sorted(VALID_STATUSES)})")

    contact = record.get("prior_contact_checked")
    if contact is not None and contact not in VALID_CONTACT_STATES:
        errors.append(
            f"{path}: invalid prior_contact_checked '{contact}' "
            f"(expected one of {sorted(VALID_CONTACT_STATES)})"
        )

    if "submitted" in record:
        errors.append(
            f"{path}: deprecated 'submitted' boolean present — "
            f"status alone is the source of truth now, remove this field"
        )

    if status in SENT_STATUSES:
        if not record.get("attached_cv_file"):
            errors.append(f"{path}: status is '{status}' but attached_cv_file is missing")
        if "attached_cv_verified" not in record:
            errors.append(
                f"{path}: status is '{status}' but attached_cv_verified is missing "
                f"(use 'unknown' if it genuinely can't be confirmed, never guess 'yes')"
            )
        elif record["attached_cv_verified"] not in VALID_VERIFIED_STATES:
            errors.append(
                f"{path}: invalid attached_cv_verified '{record['attached_cv_verified']}' "
                f"(expected one of {sorted(VALID_VERIFIED_STATES)})"
            )

    next_action = record.get("next_action")
    next_action_at = record.get("next_action_at")
    if bool(next_action) != bool(next_action_at):
        errors.append(f"{path}: next_action and next_action_at must be set together, or both left out")

    if status in STATUSES_NEEDING_NEXT_ACTION and not (next_action and next_action_at):
        errors.append(
            f"{path}: status is '{status}' but has no next_action/next_action_at -- "
            f"an active thread with no tracked next step goes stale silently"
        )

    for field in DATE_FIELDS:
        value = record.get(field)
        if value is not None and not is_valid_iso_date(value):
            errors.append(f"{path}: field '{field}' is not a valid ISO 8601 date: {value!r}")

    return errors


def main() -> None:
    args = sys.argv[1:]
    if not args:
        print(__doc__)
        sys.exit(1)

    if args[0] == "--all":
        paths = sorted(Path("applications").glob("*/record.json"))
    else:
        paths = [Path(a) for a in args]

    legacy_count = 0
    real_errors: list[str] = []

    for path in paths:
        try:
            record = json.loads(path.read_text())
        except (OSError, json.JSONDecodeError) as e:
            real_errors.append(f"{path}: could not read/parse — {e}")
            continue

        for result in validate(record, str(path)):
            if result.startswith("LEGACY "):
                legacy_count += 1
            else:
                real_errors.append(result)

    if legacy_count:
        print(f"{legacy_count} legacy record(s) (schema_version != 2) skipped, not validated.")
    for e in real_errors:
        print(e)

    if real_errors:
        sys.exit(1)

    print(f"OK — {len(paths) - legacy_count} v2 record(s) valid.")


if __name__ == "__main__":
    main()
