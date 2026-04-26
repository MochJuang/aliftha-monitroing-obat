#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
INPUT_DIR="$ROOT_DIR/docs/mermaid/bab2"
OUTPUT_DIR="$ROOT_DIR/docs/mermaid/bab2/png"

if ! command -v mmdc >/dev/null 2>&1; then
  echo "mmdc tidak ditemukan."
  echo "Install dulu dengan:"
  echo "npm install -g @mermaid-js/mermaid-cli"
  exit 1
fi

mkdir -p "$OUTPUT_DIR"

for file in "$INPUT_DIR"/*.mmd; do
  [ -e "$file" ] || continue

  name="$(basename "$file" .mmd)"
  output="$OUTPUT_DIR/$name.png"

  echo "Exporting $name -> $output"
  mmdc -i "$file" -o "$output" -b transparent -s 2
done

echo "Selesai. File PNG ada di: $OUTPUT_DIR"
