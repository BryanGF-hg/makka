#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"
[ -d .venv ] || python3 -m venv .venv
source .venv/bin/activate
pip install -q -r requirements.txt

curl -s http://localhost:11434/api/tags >/dev/null \
  && echo "✅ Ollama OK" \
  || echo "⚠️  Ollama no responde en 11434"

exec uvicorn app:app --host 127.0.0.1 --port 8001 --reload
