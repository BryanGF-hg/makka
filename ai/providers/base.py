from abc import ABC, abstractmethod
from schemas import ChatMessage, ProviderResult


class Provider(ABC):
    name: str = "base"

    @abstractmethod
    async def chat(
        self,
        messages: list[ChatMessage],
        model: str | None = None,
        temperature: float = 0.7,
        max_tokens: int = 1024,
    ) -> ProviderResult:
        ...
