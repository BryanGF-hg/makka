from providers.ollama_provider import OllamaProvider
from providers.openrouter_provider import OpenRouterProvider
from providers.gemini_provider import GeminiProvider
from providers.groq_provider import GroqProvider

ollama = OllamaProvider()
qwen   = OpenRouterProvider()
gemini = GeminiProvider()
groq   = GroqProvider()

BY_NAME = {"ollama": ollama, "qwen": qwen, "gemini": gemini, "groq": groq}
