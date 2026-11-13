import tiktoken

_ENC = tiktoken.get_encoding("cl100k_base")


def count_tokens(text: str) -> int:
    return len(_ENC.encode(text))


def split_by_tokens(text: str, max_tokens: int, overlap: int = 0) -> list[str]:
    tokens = _ENC.encode(text)
    if len(tokens) <= max_tokens:
        return [text]
    chunks, step = [], max_tokens - overlap
    for i in range(0, len(tokens), step):
        chunks.append(_ENC.decode(tokens[i : i + max_tokens]))
        if i + max_tokens >= len(tokens):
            break
    return chunks
