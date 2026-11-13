# Estructura del proyecto

```
002-makka/
├── chat.php                    ← UI principal (Apache)
├── ai-control.php              ← start/stop del backend Python
├── ai-logs.php                 ← lectura del log de uvicorn
│
├── ai/                         ← backend Python (NO accesible por web)
│   ├── .env                    ← configuración real (gitignore)
│   ├── .env.example            ← plantilla (sí se sube)
│   ├── app.py                  ← FastAPI: /health, /chat, /chat/stream
│   ├── config.py               ← lectura de .env con pydantic-settings
│   ├── schemas.py              ← modelos Pydantic request/response
│   ├── tokens.py               ← tiktoken (contar / partir)
│   ├── router.py               ← 4 estrategias multi-agente
│   ├── run.sh                  ← arranque manual del backend
│   ├── requirements.txt
│   └── providers/
│       ├── __init__.py         ← instancia los 4 providers
│       ├── base.py             ← interfaz abstracta
│       ├── ollama_provider.py
│       ├── openrouter_provider.py
│       ├── gemini_provider.py
│       └── groq_provider.py
│
├── deploy/
│   └── apache-makka-ai.conf    ← vhost + proxy a :8001
│
├── install.md
└── logs.md

## Flujo de datos

```
chat.php ──HTTP──> ai-control.php ──start──> uvicorn (FastAPI :8001)
   │
   └──POST /chat/stream──> FastAPI
                             │
                             ├──> providers/ollama_provider    ──> Ollama :11434
                             ├──> providers/openrouter_provider ─> OpenRouter
                             ├──> providers/gemini_provider    ──> Google AI Studio
                             └──> providers/groq_provider      ──> Groq API
```

## Configuración: dónde se cambia cada cosa

| Qué | Dónde |
|---|---|
| Claves API | `ai/.env` (nunca en código) |
| Modelo de cada provider | `ai/.env` (`*_MODEL`) — ver `config.py` para nombres de campo |
| Estrategia por defecto | `ai/.env` (`STRATEGY`) |
| Contexto de Ollama | `ai/.env` (`OLLAMA_CONTEXT`) |
| Puerto del backend | env var `MAKKA_AI_PORT` leída por `ai-control.php` |
| Directorio del backend | env var `MAKKA_AI_DIR` leída por `ai-control.php` |

## Variables de entorno de `.env`

Pydantic-settings las empareja case-insensitive con los campos de `config.py`:

- `OLLAMA_MODEL`     ↔ `ollama_model`
- `OPENROUTER_MODEL` ↔ `openrouter_model`
- `GEMINI_MODEL`     ↔ `gemini_model`
- `GROQ_MODEL`       ↔ `groq_model`
- `OPENROUTER_API_KEY`, `GEMINI_API_KEY`, `GROQ_API_KEY`
- `STRATEGY`, `MAX_TOKENS`, `CHUNK_OVERLAP`

**Prioridad al resolver un valor:** variable de entorno del proceso > `.env` > default en `config.py`.

## Añadir un nuevo provider (para cambios de código, no solo de modelo)

1. Crear `ai/providers/mi_provider.py` heredando de `Provider` (ver `base.py`).
2. Registrarlo en `ai/providers/__init__.py`.
3. Añadirlo a `FALLBACK_MAP` en `ai/router.py`.
4. Ampliar el `Literal` de `fallback_order` en `ai/schemas.py`.
5. Reportarlo en `/health` de `ai/app.py`.
6. Añadir su `<article class="bot-card">` en `chat.php`.

Cambiar solo el **modelo** dentro de un provider existente **NO** requiere tocar código: basta con `ai/.env`.

## Excluido del repo (`.gitignore`)

- `ai/.env`
- `ai/.venv/`
- `ai/__pycache__/`
- `*.log`
- Archivos personales: `zideas.txt`, `Untitled File`, `versiones de chat/`, `mds/`, `logs.md` (si lo prefieres mantener fuera)    
