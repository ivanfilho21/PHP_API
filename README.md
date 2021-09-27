# PHP_API
A simple Rest API made with PHP.


Still in progres...

## Endpoints

Method | Endpoint | Params | Description
-- | -- | -- | -- |
GET | /ping | - | Returns "PONG".
GET | / | idNota | Returns a note.
GET | /list | paginaAtual, maxNotas | Returns a list of notes limited by page and max per page.
POST | / | titulo, conteudo | Creates a note.