import asyncio
import json as _json

from fastapi.responses import StreamingResponse

from config import settings
from schemas import ChatMessage, ChatRequest, ChatResponse, ProviderResult
from providers import ollama, qwen, gemini, groq
from tokens import count_tokens, split_by_tokens


FALLBACK_MAP = {"qwen": qwen, "gemini": gemini, "groq": groq}


# ═══════════════════════════════════════════════════════════════
# Helpers
# ═══════════════════════════════════════════════════════════════

def _valid(r: ProviderResult) -> bool:
    return (not r.error) and bool((r.content or "").strip() or (r.thinking or "").strip())


def _body(r: ProviderResult) -> str:
    return (r.content or "").strip() or (r.thinking or "").strip()


def _diag(results: list[ProviderResult]) -> str:
    lines = []
    for r in results:
        if r.error:
            lines.append(f"❌ {r.provider}: {r.error[:140]}")
        elif not (r.content or r.thinking):
            lines.append(f"⚠️ {r.provider}: respuesta vacía")
        else:
            lines.append(f"✅ {r.provider}: {len(r.content)}c")
    return "\n".join(lines)


async def _fallback(messages, order, **kw) -> ProviderResult:
    last_error = "Todos los fallbacks fallaron"
    for name in order:
        p = FALLBACK_MAP.get(name)
        if not p:
            continue
        res = await p.chat(messages, **kw)
        if _valid(res):
            return res
        if res.error:
            last_error = f"{name}: {res.error}"
    return ProviderResult(provider="none", model="none", content="", error=last_error)


def _sys_user(messages):
    system = "\n".join(m.content for m in messages if m.role == "system")
    user   = "\n".join(m.content for m in messages if m.role == "user")
    return system, user


# ═══════════════════════════════════════════════════════════════
# chunk_parallel
# ═══════════════════════════════════════════════════════════════

async def _answer_chunk_parallel(req: ChatRequest) -> ChatResponse:
    system, user = _sys_user(req.messages)
    full = (system + "\n\n" if system else "") + user
    total = count_tokens(full)

    # CASO 1: cabe en el contexto → Ollama directo
    if total <= settings.ollama_context:
        res = await ollama.chat(req.messages,
                                temperature=req.temperature,
                                max_tokens=req.max_output_tokens)
        if not _valid(res):
            res = await _fallback(req.messages, req.fallback_order,
                                  temperature=req.temperature,
                                  max_tokens=req.max_output_tokens)
        return ChatResponse(
            strategy="ollama_direct" if res.provider == "ollama" else f"fallback → {res.provider}",
            answer=_body(res),
            thinking=res.thinking or "",
            chunks=1,
            results=[res],
        )

    # CASO 2: trocear + repartir
    chunks = split_by_tokens(full, settings.ollama_context - 512, settings.chunk_overlap)
    cycle = [ollama, qwen, gemini, groq]

    tasks = [
        cycle[i % len(cycle)].chat(
            [ChatMessage(role="user",
                         content=f"Resume el siguiente fragmento (parte {i+1}/{len(chunks)}), "
                                 f"conservando todos los datos técnicos:\n\n{chunk}")],
            temperature=0.3, max_tokens=800,
        )
        for i, chunk in enumerate(chunks)
    ]
    results = await asyncio.gather(*tasks)

    final_chunks: list[ProviderResult] = []
    for i, r in enumerate(results):
        if not _valid(r):
            r = await _fallback(
                [ChatMessage(role="user",
                             content=f"Resume el fragmento {i+1}/{len(chunks)}:\n\n{chunks[i]}")],
                req.fallback_order, temperature=0.3, max_tokens=800,
            )
        final_chunks.append(r)

    resumenes = "\n\n---\n\n".join(
        f"[Parte {i+1} · {r.provider}]:\n{_body(r)}"
        for i, r in enumerate(final_chunks) if _valid(r)
    )

    if not resumenes.strip():
        return ChatResponse(
            strategy="chunk_parallel",
            answer="Ningún provider respondió a los fragmentos.\n\n" + _diag(final_chunks),
            chunks=len(chunks), results=final_chunks,
        )

    compose = [
        ChatMessage(role="system",
                    content="Eres un sintetizador. Recibirás resúmenes parciales de un "
                            "documento largo. Devuelve una respuesta única, coherente y "
                            "completa, sin mencionar que venían en partes."),
        ChatMessage(role="user", content=resumenes),
    ]
    if count_tokens(resumenes) > settings.ollama_context - 512:
        # Solo cogemos los chunks que quepan en el contexto, en orden
        parts = split_by_tokens(resumenes, settings.ollama_context - 512, 0)
        compact = parts[0]  # el primero ya cabe por definición
        # (mejor que truncar sin más: le avisamos al modelo)
        compose[-1] = ChatMessage(
            role="user",
            content=compact + "\n\n[Nota: el resto del documento se omitió por límite de contexto]"
        )

    final = await ollama.chat(compose,
                              temperature=req.temperature,
                              max_tokens=req.max_output_tokens)
    if not _valid(final):
        final = await _fallback(compose, req.fallback_order,
                                temperature=req.temperature,
                                max_tokens=req.max_output_tokens)

    return ChatResponse(
        strategy="chunk_parallel",
        answer=_body(final),
        thinking=final.thinking or "",
        chunks=len(chunks),
        results=final_chunks + [final],
    )


# ═══════════════════════════════════════════════════════════════
# fallback
# ═══════════════════════════════════════════════════════════════

async def _answer_fallback(req: ChatRequest) -> ChatResponse:
    res = await ollama.chat(req.messages,
                            temperature=req.temperature,
                            max_tokens=req.max_output_tokens)
    if not _valid(res):
        res = await _fallback(req.messages, req.fallback_order,
                              temperature=req.temperature,
                              max_tokens=req.max_output_tokens)
    return ChatResponse(
        strategy=f"fallback → {res.provider}",
        answer=_body(res),
        thinking=res.thinking or "",
        chunks=1, results=[res],
    )


# ═══════════════════════════════════════════════════════════════
# consensus
# ═══════════════════════════════════════════════════════════════

async def _answer_consensus(req: ChatRequest) -> ChatResponse:
    providers = [ollama, qwen, gemini, groq]
    results = await asyncio.gather(*[
        p.chat(req.messages, temperature=req.temperature, max_tokens=req.max_output_tokens)
        for p in providers
    ])
    ok = [r for r in results if _valid(r)]
    if not ok:
        return ChatResponse(
            strategy="consensus",
            answer="Ningún provider respondió.\n\n" + _diag(results),
            results=results,
        )
    blob = "\n\n---\n\n".join(f"[{r.provider}]:\n{_body(r)}" for r in ok)
    merge = [
        ChatMessage(role="system",
                    content="Recibes varias respuestas a la misma pregunta. Fusiónalas "
                            "en UNA respuesta final completa, precisa y coherente."),
        ChatMessage(role="user", content=blob),
    ]
    final = await ollama.chat(merge, temperature=0.3, max_tokens=req.max_output_tokens)
    if not _valid(final):
        final = ok[0]
    return ChatResponse(
        strategy="consensus",
        answer=_body(final),
        thinking=final.thinking or "",
        results=results + [final],
    )


# ═══════════════════════════════════════════════════════════════
# best_of
# ═══════════════════════════════════════════════════════════════

async def _answer_best_of(req: ChatRequest) -> ChatResponse:
    providers = [ollama, qwen, gemini, groq]
    results = await asyncio.gather(*[
        p.chat(req.messages, temperature=req.temperature, max_tokens=req.max_output_tokens)
        for p in providers
    ])
    ok = [r for r in results if _valid(r)]
    if not ok:
        return ChatResponse(
            strategy="best_of",
            answer="Ningún provider respondió.\n\n" + _diag(results),
            results=results,
        )
    if len(ok) == 1:
        chosen = ok[0]
        return ChatResponse(
            strategy=f"best_of → {chosen.provider}",
            answer=_body(chosen), thinking=chosen.thinking or "",
            results=results,
        )
    judge_input = "\n\n".join(f"[{i}] ({r.provider}):\n{_body(r)}" for i, r in enumerate(ok))
    judge = [
        ChatMessage(role="system",
                    content="Eres un juez imparcial. Recibes varias respuestas "
                            "numeradas. Devuelve SOLO el número de la mejor "
                            "respuesta (0, 1, 2…), sin explicación."),
        ChatMessage(role="user", content=judge_input),
    ]
    verdict = await ollama.chat(judge, temperature=0, max_tokens=10)
    idx = 0
    if not verdict.error and verdict.content:
        try:
            idx = int(verdict.content.strip().split()[0])
            if not (0 <= idx < len(ok)): idx = 0
        except Exception:
            idx = 0
    chosen = ok[idx]
    return ChatResponse(
        strategy=f"best_of → {chosen.provider}",
        answer=_body(chosen), thinking=chosen.thinking or "",
        results=results,
    )


# ═══════════════════════════════════════════════════════════════
# Entry
# ═══════════════════════════════════════════════════════════════

async def answer(req: ChatRequest) -> ChatResponse:
    strat = req.strategy or settings.strategy
    if strat == "fallback":   return await _answer_fallback(req)
    if strat == "consensus":  return await _answer_consensus(req)
    if strat == "best_of":    return await _answer_best_of(req)
    return await _answer_chunk_parallel(req)


# ═══════════════════════════════════════════════════════════════
# Streaming
# ═══════════════════════════════════════════════════════════════
async def stream_answer(req: ChatRequest) -> StreamingResponse:
    async def event_generator():
        try:
            strat = req.strategy or settings.strategy

            # ── Pre-chequeo: ¿cabe el input en el contexto de Ollama? ──
            system, user = _sys_user(req.messages)
            full = (system + "\n\n" if system else "") + user
            total_tokens = count_tokens(full)
            fits = total_tokens <= (settings.ollama_context - 256)

            # ── Chunk_parallel / fallback ──
            if strat in ("chunk_parallel", "fallback", None):
                if not fits:
                    # No cabe → NO hacer stream directo.
                    # Delegamos al handler no-streaming que sí trocea.
                    if strat == "fallback":
                        res = await _answer_fallback(req)
                    else:
                        res = await _answer_chunk_parallel(req)

                    results_data = [{
                        "provider": r.provider,
                        "model": r.model,
                        "error": r.error,
                        "elapsed_ms": r.elapsed_ms,
                        "tokens_in": r.tokens_in,
                        "tokens_out": r.tokens_out,
                    } for r in res.results]

                    if res.thinking:
                        yield f"data: {_json.dumps({'thinking': res.thinking, 'content': ''})}\n\n"

                    yield f"data: {_json.dumps({
                        'thinking': '',
                        'content': res.answer,
                        'done': True,
                        'strategy': res.strategy,
                        'results': results_data,
                    })}\n\n"
                    return

                # ── Cabe en contexto: streaming directo a Ollama ──
                got_content = False
                last_chunk = {}
                async for chunk in ollama.stream_chat(
                    req.messages,
                    temperature=req.temperature,
                    max_tokens=req.max_output_tokens,
                ):
                    if isinstance(chunk, dict) and "error" in chunk:
                        yield f"data: {_json.dumps({'error': chunk['error']})}\n\n"
                        break
                    if isinstance(chunk, dict) and chunk.get("done"):
                        last_chunk = chunk
                    msg = chunk.get("message", {}) if isinstance(chunk, dict) else {}
                    thinking = msg.get("thinking", "") or ""
                    content = msg.get("content", "") or ""
                    if thinking or content:
                        if content:
                            got_content = True
                        yield f"data: {_json.dumps({'thinking': thinking, 'content': content})}\n\n"

                tokens_in = int(last_chunk.get("prompt_eval_count", 0) or 0)
                tokens_out = int(last_chunk.get("eval_count", 0) or 0)

                if not got_content:
                    res = await _answer_fallback(req)
                    results_data = [{
                        "provider": r.provider, "model": r.model,
                        "error": r.error, "elapsed_ms": r.elapsed_ms,
                        "tokens_in": r.tokens_in, "tokens_out": r.tokens_out,
                    } for r in res.results]
                    yield f"data: {_json.dumps({
                        'thinking': res.thinking, 'content': res.answer,
                        'done': True, 'strategy': res.strategy,
                        'results': results_data,
                    })}\n\n"
                    return

                yield f"data: {_json.dumps({
                    'done': True,
                    'strategy': 'stream_direct',
                    'results': [{
                        'provider': 'ollama',
                        'model': settings.ollama_model,
                        'error': None,
                        'elapsed_ms': 0,
                        'tokens_in': tokens_in,
                        'tokens_out': tokens_out,
                    }],
                })}\n\n"
                return

            # ── consensus / best_of (no streaming, con todos los results) ──
            res = await answer(req)
            results_data = [{
                "provider": r.provider, "model": r.model,
                "error": r.error, "elapsed_ms": r.elapsed_ms,
                "tokens_in": r.tokens_in, "tokens_out": r.tokens_out,
            } for r in res.results]
            yield f"data: {_json.dumps({
                'thinking': res.thinking,
                'content': res.answer,
                'done': True,
                'strategy': res.strategy,
                'results': results_data,
            })}\n\n"

        except Exception as e:
            yield f"data: {_json.dumps({'error': f'{type(e).__name__}: {e}'})}\n\n"

    return StreamingResponse(event_generator(), media_type="text/event-stream")
