import time
import httpx
from config import settings
from schemas import ChatMessage, ProviderResult
from providers.base import Provider

class OpenRouterProvider(Provider):
    name = "qwen"

    async def chat(self, messages, model=None, temperature=0.7, max_tokens=1024):
        t0 = time.time()
        model = model or settings.openrouter_model
        if not settings.openrouter_api_key:
            return ProviderResult(provider=self.name, model=model, content="", error="OPENROUTER_API_KEY no configurada")
        
        headers = {
            "Authorization": f"Bearer {settings.openrouter_api_key}",
            "HTTP-Referer": "http://localhost",
            "X-Title": "Makka AI",
            "Content-Type": "application/json",
        }
        payload = {
            "model": model,
            "messages": [m.model_dump() for m in messages],
            "temperature": temperature,
            "max_tokens": max_tokens,
        }
        try:
            async with httpx.AsyncClient(timeout=90) as cli:
                r = await cli.post(settings.openrouter_url, json=payload, headers=headers)
                r.raise_for_status()
                data = r.json()
            
            message = data["choices"][0]["message"]
            content = message.get("content", "")
            thinking = message.get("reasoning", "") # Algunos modelos gratis dan reasoning
            
            usage = data.get("usage", {})
            return ProviderResult(
                provider=self.name, model=model, 
                content=content, thinking=thinking,
                tokens_in=usage.get("prompt_tokens", 0),
                tokens_out=usage.get("completion_tokens", 0),
                elapsed_ms=int((time.time() - t0) * 1000),
            )
        except Exception as e:
            return ProviderResult(
                provider=self.name, model=model, content="",
                elapsed_ms=int((time.time() - t0) * 1000),
                error=f"{type(e).__name__}: {e}",
            )
