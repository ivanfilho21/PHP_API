<?php

use System\Database as DB;

$router->get('/ping', function() {
    echo json_encode("PONG!");
});

$router->get('/', 'NoteController@index', ['idNota']);
$router->get('/list', 'NoteController@list', ['paginaAtual', 'maxNotas']);
$router->post('/', 'NoteController@create');
$router->delete('/', 'NoteController@remove', 'idNota');