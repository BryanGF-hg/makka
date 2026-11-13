import re
import time
import httpx
from config import settings
from schemas import ChatMessage, ProviderResult
from providers.base import Provider

# Modelos que sabemos que exponen razonamiento
THINKING_MODELS = ("r1", "think", "reason", "qwq", "mimo", "mimo-v2", "deepseek")

def _es_thinking(model: str) -> bool:
    m = model.lower()
    return any(k in m for k in THINKING_MODELS)


def _separar_thinking(content: str) -> tuple[str, str]:
    """
    Si `content` trae el razonamiento embebido, lo separa.
    Soporta:
      1. Tags  ... 
      2. Tags <thinking>…</thinking>
      3. Tags <reasoning>…</reasoning>
      4. Prefijo "Thinking: …\n\n" o "Reasoning: …\n\n"
    Devuelve (content_limpio, thinking).
    """
    if not content:
        return "", ""

    # 1)  ... 
    m = re.search(r"<think(?:ing)?>(.*?)</think(?:ing)?>", content, re.S | re.I)
    if m:
        thinking = m.group(1).strip()
        clean    = re.sub(r"<think(?:ing)?>.*?</think(?:ing)?>", "", content, flags=re.S | re.I).strip()
        return clean, thinking

    # 2) <reasoning>…</reasoning>
    m = re.search(r"<reasoning>(.*?)</reasoning>", content, re.S | re.I)
    if m:
        thinking = m.group(1).strip()
        clean    = re.sub(r"<reasoning>.*?</reasoning>", "", content, flags=re.S | re.I).strip()
        return clean, thinking

    # 3) Prefijo textual
    m = re.match(r"^\s*(?:thinking|reasoning|razonamiento)\s*:\s*(.+?)(?:\n\n|\Z)", content, re.S | re.I)
    if m:
        thinking = m.group(1).strip()
        clean    = content[m.end():].lstrip("\n").strip()
        return clean, thinking

    return content, ""


class OllamaProvider(Provider):
    name = "ollama"

    async def chat(self, messages, model=None, temperature=0.7, max_tokens=1024):
        t0 = time.time()
        model = model or settings.ollama_model

        base_payload = {
            "model": model,
            "messages": [m.model_dump() for m in messages],
            "stream": False,
            "options": {
                "temperature": temperature,
                "num_ctx": settings.ollama_context,
                "num_predict": max_tokens,
            },
        }

        # Pedir thinking solo si el modelo lo soporta
        payload = dict(base_payload)
        if _es_thinking(model):
            payload["think"] = True

        data = None
        async with httpx.AsyncClient(timeout=settings.ollama_timeout) as cli:
            try:
                r = await cli.post(f"{settings.ollama_url}/api/chat", json=payload)
                if r.status_code == 400 and "think" in payload:
                    # Ollama viejo → reintenta sin think
                    payload = dict(base_payload)
                    r = await cli.post(f"{settings.ollama_url}/api/chat", json=payload)
                r.raise_for_status()
                data = r.json()
            except Exception as e:
                return ProviderResult(
                    provider=self.name, model=model, content="",
                    elapsed_ms=int((time.time() - t0) * 1000),
                    error=f"{type(e).__name__}: {e}",
                )

        msg = data.get("message", {}) or {}
        raw_content   = (msg.get("content") or "").strip()
        native_think  = (msg.get("thinking") or "").strip()   # campo nuevo de Ollama

        clean, from_content = _separar_thinking(raw_content)
        thinking = native_think or from_content

        return ProviderResult(
            provider=self.name, model=model,
            content=clean, thinking=thinking,
            tokens_in=data.get("prompt_eval_count", 0),
            tokens_out=data.get("eval_count", 0),
            elapsed_ms=int((time.time() - t0) * 1000),
        )
    async def stream_chat(self, messages, model=None, temperature=0.7, max_tokens=1024):
        import json
        model = model or settings.ollama_model
        payload = {
            "model": model,
            "messages": [m.model_dump() for m in messages],
            "stream": True,
            "options": {
                "temperature": temperature,
                "num_ctx": settings.ollama_context,
                "num_predict": max_tokens,
            },
        }
        if _es_thinking(model):
            payload["think"] = True

        async with httpx.AsyncClient(timeout=settings.ollama_timeout) as cli:
            try:
                async with cli.stream("POST", f"{settings.ollama_url}/api/chat", json=payload) as r:
                    if r.status_code == 400 and "think" in payload:
                        payload.pop("think")
                        async with cli.stream("POST", f"{settings.ollama_url}/api/chat", json=payload) as r2:
                            async for line in r2.aiter_lines():
                                if line:
                                    yield json.loads(line)
                    else:
                        async for line in r.aiter_lines():
                            if line:
                                yield json.loads(line)
            except Exception as e:
                yield {"error": str(e)}        
