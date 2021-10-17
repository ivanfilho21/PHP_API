# PHP_API
A simple public Restful API created using pure PHP.
It supports HTML based clients.

# Base URL
### https://salty-hamlet-85028.herokuapp.com

## Endpoints

Method | Endpoint | Query Params | Request Params | Description
-- | -- | -- | -- | -- 
GET | /ping | - | - | Returns "PONG".
GET | / | idNota | - | Returns a note.
GET | /list | paginaAtual, maxNotas | - | Returns a list of notes limited by page and max per page.
POST | / | - | titulo, conteudo | Creates a note.
PUT | / | idNota | titulo, conteudo | Updates a note.
DELETE | / | idNota | - | Deletes a note.
POST | /update/{idNota} | - | titulo, conteudo | Updates a note.
POST | /delete/{idNota} | - | - | Deletes a note.


## Exemplo de Comunicação com a API utilizando Javascript


Antes de qualquer coisa, você precisa saber a URL base da API.


```javascript
var baseUrl = "https://salty-hamlet-85028.herokuapp.com";
```

Cada endpoint, ou caminho, da API possui um método de conexão HTTP, como por exemplo o GET.
Sabendo-se o método e o endpoint, já podemos fazer uma conexão com a URL.

Agora abra a conexão com essa URL, da seguinte forma:

```javascript
var baseUrl = "https://salty-hamlet-85028.herokuapp.com";
var metodo = "GET";
var endpoint = "/list";
var conexao = new XMLHttpRequest();

conexao.open(metodo, baseUrl + endpoint, true);
conexao.send();
```

Isso deve funcionar, porém precisamos receber os dados que serão trazidos da API.
Para isso vamos criar um callback que será invocado assim que a API estiver pronta para retorná-los.

```javascript
var callback = function() {
    var response = this.responseText;
    console.log(response)
};
```


O código completo ficará assim:

```javascript
var callback = function() {
    var response = this.responseText;
    console.log(response)
};

var baseUrl = "https://salty-hamlet-85028.herokuapp.com";
var metodo = "GET";
var endpoint = "/list";
var conexao = new XMLHttpRequest();

conexao.onload = callback;
conexao.open(metodo, baseUrl + endpoint, true);
conexao.send();
```
