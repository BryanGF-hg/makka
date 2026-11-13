import time
import httpx
from config import settings
from schemas import ChatMessage, ProviderResult
from providers.base import Provider
from tokens import count_tokens


class GroqProvider(Provider):
    name = "groq"

    # Groq Free tier: 8000 TPM para gpt-oss-120b
    # Dejamos margen para la respuesta → truncamos input a 4000
    MAX_INPUT_TOKENS = 4000

    async def chat(self, messages, model=None, temperature=0.7, max_tokens=1024):
        t0 = time.time()
        model = model or settings.groq_model

        if not settings.groq_api_key:
            return ProviderResult(
                provider=self.name, model=model, content="",
                error="GROQ_API_KEY no configurada",
            )

        # ── Truncar historial para respetar TPM ──
        msgs = [m.model_dump() for m in messages]
        msgs = self._truncate_history(msgs, self.MAX_INPUT_TOKENS)

        headers = {
            "Authorization": f"Bearer {settings.groq_api_key}",
            "Content-Type": "application/json",
        }
        payload = {
            "model": model,
            "messages": msgs,
            "temperature": temperature,
            "max_tokens": max_tokens,
        }

        try:
            async with httpx.AsyncClient(timeout=90) as cli:
                r = await cli.post(settings.groq_url, json=payload, headers=headers)
                r.raise_for_status()
                data = r.json()

            message = data["choices"][0]["message"]
            content = message.get("content", "") or ""
            thinking = message.get("reasoning", "") or ""
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

    @staticmethod
    def _truncate_history(msgs: list, max_tokens: int) -> list:
        """
        Recorta el historial para no superar max_tokens.
        - Preserva siempre el primer `system` si existe
        - Preserva los mensajes más recientes (últimos turnos)
        - Trunca el contenido del último mensaje si él solo excede el budget
        """
        if not msgs:
            return msgs

        total = sum(count_tokens(m.get("content", "") or "") for m in msgs)
        if total <= max_tokens:
            return msgs

        system_msg = None
        rest = []
        for m in msgs:
            if m.get("role") == "system" and system_msg is None:
                system_msg = m
            else:
                rest.append(m)

        result = []
        budget = max_tokens
        if system_msg:
            result.append(system_msg)
            budget -= count_tokens(system_msg.get("content", "") or "")

        kept = []
        for m in reversed(rest):
            t = count_tokens(m.get("content", "") or "")
            if t > budget:
                # Último intento: truncar el content
                if not kept:
                    content = m.get("content", "") or ""
                    max_chars = max(budget * 4, 200)
                    if len(content) > max_chars:
                        kept.append({**m, "content": content[-max_chars:]})
                break
            kept.append(m)
            budget -= t

        result.extend(reversed(kept))
        return result
