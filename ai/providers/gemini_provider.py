import time
import httpx
from config import settings
from schemas import ChatMessage, ProviderResult
from providers.base import Provider

class GeminiProvider(Provider):
    name = "gemini"

    async def chat(self, messages, model=None, temperature=0.7, max_tokens=1024):
        t0 = time.time()
        model = model or settings.gemini_model
        if not settings.gemini_api_key:
            return ProviderResult(provider=self.name, model=model, content="", error="GEMINI_API_KEY no configurada")

        system = "\n".join(m.content for m in messages if m.role == "system").strip()
        contents = [
            {"role": "user" if m.role == "user" else "model", "parts": [{"text": m.content}]}
            for m in messages if m.role != "system"
        ]
        payload = {
            "contents": contents,
            "generationConfig": {"temperature": temperature, "maxOutputTokens": max_tokens},
        }
        if system:
            payload["systemInstruction"] = {"parts": [{"text": system}]}

        url = f"{settings.gemini_url}/{model}:generateContent?key={settings.gemini_api_key}"
        
        try:
            async with httpx.AsyncClient(timeout=90) as cli:
                r = await cli.post(url, json=payload)
                r.raise_for_status()
                data = r.json()
            
            content = data["candidates"][0]["content"]["parts"][0]["text"]
            usage = data.get("usageMetadata", {})
            return ProviderResult(
                provider=self.name, model=model, content=content,
                tokens_in=usage.get("promptTokenCount", 0),
                tokens_out=usage.get("candidatesTokenCount", 0),
                elapsed_ms=int((time.time() - t0) * 1000),
            )
        except Exception as e:
            return ProviderResult(
                provider=self.name, model=model, content="",
                elapsed_ms=int((time.time() - t0) * 1000),
                error=f"{type(e).__name__}: {e}",
            )
