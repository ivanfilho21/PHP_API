<?php

namespace System;

class Router {
    private $debug;

    function __construct($debug = false) {
        $this->debug = $debug;

        $key = 'endpoint';
        $exists = array_key_exists($key, $_REQUEST);
        $endpoint = $exists ? $_REQUEST[$key] : '';
        $endpoint = rtrim($endpoint, '/');
        

        if ($debug) {
            echo "Filtered Endpoint: $endpoint<br>";
        }


        $response = '';

        switch ($endpoint) {
            case '':
                $response = 'Home';
                break;
            case 'aa':
                $response = 'You win';
                break;
            default:
                return http_response_code(404);
        }

        echo json_encode($response);
    }
}