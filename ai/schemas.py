from typing import Literal
from pydantic import BaseModel, Field


class ChatMessage(BaseModel):
    role: Literal["system", "user", "assistant"]
    content: str


class ChatRequest(BaseModel):
    messages: list[ChatMessage]
    model: str | None = None
    temperature: float = 0.7
    max_output_tokens: int = 1024
    strategy: Literal["chunk_parallel", "fallback", "consensus", "best_of"] | None = None
    fallback_order: list[Literal["qwen", "gemini", "groq"]] = Field(
        default_factory=lambda: ["qwen", "gemini", "groq"]
    )
    think: bool = True      # ← NUEVO: pedir thinking si el modelo lo soporta


class ProviderResult(BaseModel):
    provider: str
    model: str
    content: str
    thinking: str = ""      # ← NUEVO: razonamiento separado del contenido
    tokens_in: int = 0
    tokens_out: int = 0
    elapsed_ms: int = 0
    error: str | None = None


class ChatResponse(BaseModel):
    strategy: str
    answer: str
    thinking: str = ""      # ← NUEVO: razonamiento de la respuesta principal
    chunks: int = 1
    results: list[ProviderResult] = Field(default_factory=list)
