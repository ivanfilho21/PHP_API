# PHP_API
A simple public Rest API made with PHP.

# Base URL
### https://salty-hamlet-85028.herokuapp.com

## Endpoints

Method | Endpoint | Params | Description
-- | -- | -- | -- |
GET | /ping | - | Returns "PONG".
GET | / | idNota | Returns a note.
GET | /list | paginaAtual, maxNotas | Returns a list of notes limited by page and max per page.
POST | / | titulo, conteudo | Creates a note.
DELETE | / | idNota | Deletes a note.
