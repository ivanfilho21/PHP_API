<?php

$router->get('/ping', function($params) {
    echo json_encode($params);
}, ['params' => ['name']]);

$router->post('/test', function() {
    echo json_encode("Hello World");
});