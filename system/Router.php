<?php

namespace System;

class Router {
    private $methods = [];

    function init() {
        $currentMethod = $_SERVER['REQUEST_METHOD'];
        $currentMethod = strtolower($currentMethod);

        if ($currentMethod != 'get' && $currentMethod != 'post' && $currentMethod != 'put' && $currentMethod != 'delete') {
            return http_response_code(405);
        }
        
        $key = 'endpoint';
        $exists = array_key_exists($key, $_REQUEST);
        $endpoint = $exists ? $_REQUEST[$key] : '';
        $endpoint = htmlentities($endpoint, ENT_QUOTES, 'UTF-8');
        $endpoint = rtrim($endpoint, '/');

        //echo '<pre>'.var_export($this->methods[$currentMethod],1).'</pre>';

        foreach ($this->methods[$currentMethod] as $method) {
            $ep = trim($method['endpoint'], '/');

            if ($ep === $endpoint) {
                $callback = $method['callback'];
                if (is_callable($callback)) {
                    return call_user_func_array($callback, []);
                }
            }
        }

        return http_response_code(404);
    }

    function __call($name, $params) {
        if ($name !== 'get' && $name !== 'post' && $name !== 'put' && $name !== 'delete') {
            return;
        }

        $this->methods[$name][] = [
            'endpoint' => $params[0],
            'callback' => $params[1]
        ];
    }
}