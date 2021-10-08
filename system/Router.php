<?php

namespace System;

class Router {
    private $methods = [];

    function __call($method, $params = []) {
        // echo '<pre>' . var_export($params, true) . '</pre>';
        
        if (!$this->isMethodValid($method)) {
            throw new \Exception(sprintf('Method %s is not supported by the Router.', $method));
        }

        $endpoint = Utils::getFromArray(0, $params);
        
        if (empty($endpoint)) {
            throw new \Exception('Endpoint must not be empty.');
        }

        $callable = Utils::getFromArray(1, $params);

        if (empty($callable)) {
            throw new \Exception('You must specify either a callback or a string containing Controller and Action.');
        }

        $arguments = Utils::getFromArray(2, $params, []);
        $arguments = is_array($arguments) ? $arguments : [$arguments];
        $currentMethod = [];

        if (is_string($callable)) {
            $callable = explode('@', $callable);
            $controller = $this->getController($callable);
            $currentMethod = [
                'controller' => "\\Api\\Controller\\$controller",
                'action'     => $this->getAction($callable),
                'params'     => $arguments
            ];
        } else if (is_callable($callable)) {
            $currentMethod['callback'] = $callable;
        } else {
            throw new \Exception('You must specify either a callback or a string containing Controller and Action.');
        }
        
        $this->methods[$method][$endpoint] = $currentMethod;
    }

    function initialize() {
        $method = strtolower(Utils::getFromArray('REQUEST_METHOD', $_SERVER, ''));

        if (!$this->isMethodValid($method)) {
            return http_response_code(405);
        }

        $currentEndpoint = Utils::getFromArray('endpoint', $_GET, '');
        unset($_GET['endpoint']);

        $currentEndpoint = trim($currentEndpoint, '/');
        $currentEndpoint = htmlentities($currentEndpoint, ENT_QUOTES, 'UTF-8');
        $currentEndpoint = "/$currentEndpoint";
        $methodArray = Utils::getFromArray($method, $this->methods, []);

        // echo '<pre>' . var_export($this->methods, true) . '</pre>';
        // echo '<pre>'.var_export($this->methods,1).'</pre>';
        // echo '<pre>'.var_export($methodArray,1).'</pre>';

        $params = $method == 'post' ? ['params' => $_POST] : $_GET;
        // echo '<pre>' . var_export($params, true) . '</pre>';

        foreach ($methodArray as $endpoint => $array) {
            if (strcmp($endpoint, $currentEndpoint) == 0) {
                $callback = Utils::getFromArray('callback', $array);
                
                if (is_callable($callback)) {
                    return call_user_func_array($callback, $params);
                }

                $methodParams = [];
                foreach ($array['params'] as $p) {
                    if (isset($params[$p])) {
                        $methodParams[$p] = $params[$p];
                    }
                }

                $methodParams = empty($methodParams) ? $params : $methodParams;
                
                $controller = $array['controller'];
                $action = $array['action'];
                $obj = new $controller();

                if (is_callable([$obj, $action])) {
                    return call_user_func_array([$obj, $action], $methodParams);
                }
            }
        }

        return http_response_code(404);
    }

    private function isMethodValid($method): bool {
        $string = is_string($method);
        return $string && (strcmp($method, 'get') == 0 || strcmp($method, 'post') == 0 || strcmp($method, 'put') == 0 || strcmp($method, 'delete') == 0);
    }

    private function getController(array $array): string {
        if (count($array) >= 1) {
            $controller = $array[0];
            if (!empty($controller)) {
                return $controller;
            }
        }
        throw new \Exception('Controller not supplied.');
    }

    private function getAction(array $array): string {
        if (count($array) >= 2) {
            $action = $array[1];
            if (!empty($action)) {
                return $action;
            }
        }
        throw new \Exception('Action not supplied.');
    }

}