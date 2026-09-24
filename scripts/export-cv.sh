#!/usr/bin/env bash
# Render a CV's cv.html to cv.pdf via headless Chrome, reproducibly.
#
# Usage: scripts/export-cv.sh applications/<id>/cv.html
# Writes applications/<id>/cv.pdf next to it.
#
# Deliberately not PHP — this is workflow tooling, separate from the
# src/ portfolio prototype (see CLAUDE.md).

set -euo pipefail

if [ $# -ne 1 ]; then
  echo "Usage: $0 <path/to/cv.html>" >&2
  exit 1
fi

INPUT="$1"
if [ ! -f "$INPUT" ]; then
  echo "Error: no such file: $INPUT" >&2
  exit 1
fi

OUTPUT="${INPUT%.html}.pdf"
INPUT_ABS="$(cd "$(dirname "$INPUT")" && pwd)/$(basename "$INPUT")"

CHROME_BIN="$(command -v google-chrome || command -v chromium || command -v chromium-browser || true)"
if [ -z "$CHROME_BIN" ]; then
  echo "Error: no headless-capable Chrome/Chromium binary found." >&2
  exit 1
fi

CHROME_OUTPUT="$("$CHROME_BIN" \
  --headless=new \
  --disable-gpu \
  --no-pdf-header-footer \
  --print-to-pdf="$OUTPUT" \
  --no-sandbox \
  "file://$INPUT_ABS" 2>&1)"
CHROME_EXIT=$?

if [ $CHROME_EXIT -ne 0 ] || [ ! -s "$OUTPUT" ]; then
  echo "Error: export failed (chrome exit $CHROME_EXIT), no valid output at $OUTPUT" >&2
  echo "--- chrome output ---" >&2
  echo "$CHROME_OUTPUT" >&2
  exit 1
fi

echo "Wrote $OUTPUT"
