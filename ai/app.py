from pathlib import Path
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import HTMLResponse, FileResponse, StreamingResponse

from config import settings
from schemas import ChatRequest, ChatResponse
from router import answer, stream_answer

app = FastAPI(title="Makka AI", version="0.3.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"], allow_methods=["*"], allow_headers=["*"],
)

# opcional en app.py al inicio
if not settings.openrouter_api_key and not settings.gemini_api_key and not settings.groq_api_key:
    raise RuntimeError("Configura al menos un provider en .env")

@app.get("/health")
async def health():
    return {
        "ok": True,
        "strategy": settings.strategy,
        "ollama_model": settings.ollama_model,
        "context": settings.ollama_context,
        "providers": {
            "ollama": True,
            "qwen":   bool(settings.openrouter_api_key),
            "gemini": bool(settings.gemini_api_key),
            "groq":   bool(settings.groq_api_key),
        },
    }


@app.post("/chat", response_model=ChatResponse)
async def chat(req: ChatRequest) -> ChatResponse:
    return await answer(req)

@app.post("/chat/stream")
async def chat_stream(req: ChatRequest):
    return await stream_answer(req)

@app.get("/", response_class=HTMLResponse)
async def index():
    return "<h1>Makka AI</h1><p>Backend en marcha. La UI está en chat.php.</p>"
