<?php

namespace System;

class Router {
    private $methods = [];

    function init() {
        $currentMethod = $_SERVER['REQUEST_METHOD'];
        $currentMethod = strtolower($currentMethod);

        if ($currentMethod != 'get' && $currentMethod != 'post' && $currentMethod != 'put') {
            return http_response_code(405);
        }
        
        $key = 'endpoint';
        $exists = array_key_exists($key, $_REQUEST);
        $endpoint = $exists ? $_REQUEST[$key] : '';
        $endpoint = htmlentities($endpoint, ENT_QUOTES, 'UTF-8');
        $endpoint = rtrim($endpoint, '/');

        $params = $_SERVER['REQUEST_URI'];
        $start = strpos($params, '?');
        $params = $start == false ? null : substr($params, $start + 1);
        $params = $params == null ? [] : explode('&', $params);

        $list = [];
        foreach ($params as $p) {
            $p = explode('=', $p);
            if (!is_array($p)) {
                continue;
            }

            $list[] = [
                $p[0] => $p[1]
            ];
        }

        $params = $list;

        //echo '<pre>'.var_export($this->methods[$currentMethod],1).'</pre>';
        //echo '<pre>'.var_export($params,1).'</pre>';

        foreach ($this->methods[$currentMethod] as $method) {
            $ep = trim($method['endpoint'], '/');

            $allowedParams = [];

            if (is_string($method['allowedParams'])) {
                $allowedParams[] = $method['allowedParams'];
            } elseif (is_array($method['allowedParams'])) {
                foreach ($method['allowedParams'] as $ap) {
                    foreach ($params as $param) {
                        $key = key($param);
                        $value = $param[$key];
    
                        if ($ap == $key) {
                            $allowedParams[$key] = $value;
                            break;
                        }
                    }
                }
            }

            
            //echo '<pre>'.var_export($allowedParams,1).'</pre>';

            if ($ep === $endpoint) {
                return $method['callback']($allowedParams);
            }
        }

        return http_response_code(404);
    }

    function __call($name, $params) {
        if ($name !== 'get' && $name !== 'post' && $name !== 'put') {
            return;
        }

        $a = count($params) > 2 ? $params[2] : [];
        $key = 'params';
        $allowedParams = array_key_exists($key, $a) ? $a[$key] : [];

        $this->methods[$name][] = [
            'endpoint'      => $params[0],
            'callback'      => $params[1],
            'allowedParams' => $allowedParams
        ];
    }
}