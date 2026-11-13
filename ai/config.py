from pydantic_settings import BaseSettings, SettingsConfigDict

class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=".env", extra="ignore")

    ollama_url: str = "http://localhost:11434"
    ollama_model: str = "maternion/mimo-v2.6:9b"
    ollama_context: int = 16384
    ollama_timeout: int = 180

    openrouter_api_key: str = ""
    openrouter_model: str = "nvidia/nemotron-3-ultra"
    openrouter_url: str = "https://openrouter.ai/api/v1/chat/completions"

    gemini_api_key: str = ""
    gemini_model: str = "gemini-2.0-flash-exp"
    gemini_url: str = "https://generativelanguage.googleapis.com/v1beta/models"

    groq_api_key: str = ""
    groq_model: str = "qwen/qwen3.8-27b"
    groq_url: str = "https://api.groq.com/openai/v1/chat/completions"

    strategy: str = "chunk_parallel"
    max_tokens: int = 4096
    chunk_overlap: int = 512

settings = Settings()
