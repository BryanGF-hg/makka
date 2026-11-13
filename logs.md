#a:
tail -f /tmp/makka-ai-8001.log
#a con colores:
tail -f /tmp/makka-ai-8001.log | sed -u \
  -e 's/\(INFO:.*\)/\x1b[32m\1\x1b[0m/' \
  -e 's/\(WARNING:.*\)/\x1b[33m\1\x1b[0m/' \
  -e 's/\(ERROR.*\)/\x1b[31m\1\x1b[0m/'

#Logs directos:
sudo tail -f /var/log/apache2/access.log | grep makka

#Errores de proxy:
sudo tail -f /var/log/apache2/error.log

#que hace ollama:
sudo journalctl -u ollama -f

#reiniciar servidor
sudo pkill -f 'uvicorn app:app'
curl -s 'http://localhost/@CEAC/2DAM/Proyectos-2DAM/makka/002-makka/ai-control.php?action=start'

#health
curl -s http://localhost/makka/ai-api/health | jq


#dónde se cambia cada cosa
#Qué quiero cambiar:
Modelo de Ollama	ai/.env → OLLAMA_MODEL=... (+ ollama pull <modelo> antes)
Modelo de OpenRouter	ai/.env → OPENROUTER_MODEL=...
Modelo de Gemini	ai/.env → GEMINI_MODEL=...
Modelo de Groq	ai/.env → GROQ_MODEL=...
Claves API	ai/.env (nunca en config.py)
Contexto de Ollama	ai/.env → OLLAMA_CONTEXT=...
Estrategia por defecto	ai/.env → STRATEGY=chunk_parallel|fallback|consensus|best_of
Puerto del backend	ai-control.php → MAKKA_AI_PORT (o env var)
