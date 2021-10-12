<?php

use System\Database as DB;

$router->get('/ping', function() {
    echo json_encode("PONG!");
});

$router->get('/', 'NoteController@index', ['idNota']);
$router->post('/', 'NoteController@create');
$router->put('/', 'NoteController@update', 'idNota');
$router->delete('/', 'NoteController@remove', 'idNota');
$router->get('/list', 'NoteController@list', ['paginaAtual', 'maxNotas']);