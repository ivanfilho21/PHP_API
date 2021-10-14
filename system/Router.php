<?php

namespace System;

class Router {
    private $methods = [];

    function __call($method, $params = []) {
        // echo '<pre>' . var_export($params, true) . '</pre>';
        
        if (!$this->isMethodValid($method)) {
            throw new \InvalidArgumentException(sprintf('Method %s is not supported by the Router.', $method));
        }

        $endpoint = Utils::getFromArray(0, $params);
        
        if (empty($endpoint)) {
            throw new \InvalidArgumentException('Endpoint must not be empty.');
        }

        $callable = Utils::getFromArray(1, $params);

        if (empty($callable)) {
            throw new \InvalidArgumentException('You must specify either a callback or a string containing Controller and Action.');
        }

        $matches = [];
        $regexPattern = '/\/{([a-zA-Z]+)}/';
        preg_match_all($regexPattern, $endpoint, $matches);
        array_shift($matches);
        $endpoint = preg_replace($regexPattern, '', $endpoint);

        // echo "Endpoint: $endpoint" . '<pre>' . var_export($matches, true) . '</pre>';

        // $arguments = isset($matches[0]) ? $matches[0] : Utils::getFromArray(2, $params, []);
        $isUrlFriendly = !empty($matches[0]);
        $arguments = $isUrlFriendly ? $matches[0] : Utils::getFromArray(2, $params, []);
        $arguments = is_array($arguments) ? $arguments : [$arguments];
        $currentMethod = [];

        if (is_string($callable)) {
            $callable = explode('@', $callable);
            $controller = $this->getController($callable);
            $currentMethod = [
                'friendlyUrl' => $isUrlFriendly,
                'controller'  => "\\Api\\Controller\\$controller",
                'action'      => $this->getAction($callable),
                'params'      => $arguments
            ];
        } else if (is_callable($callable)) {
            $currentMethod['callback'] = $callable;
        } else {
            throw new \InvalidArgumentException('You must specify either a callback or a string containing Controller and Action.');
        }
        
        $this->methods[$method][$endpoint] = $currentMethod;
    }

    function initialize() {
        $method = strtolower(Utils::getFromArray('REQUEST_METHOD', $_SERVER, ''));

        if (!$this->isMethodValid($method)) {
            return http_response_code(405);
        }

        $currentEndpoint = Utils::getFromArray('endpoint', $_GET, '');
        $currentEndpoint = trim($currentEndpoint, '/');
        $currentEndpoint = htmlentities($currentEndpoint, ENT_QUOTES, 'UTF-8');
        unset($_GET['endpoint']);

        // Get arguments from endpoint String
        $explodedEndpoint = explode('/', $currentEndpoint);
        $currentEndpoint = "/$explodedEndpoint[0]";
        array_shift($explodedEndpoint);

        // echo '<pre>' . var_export($currentEndpoint, true) . '</pre>';

        $methodArray = Utils::getFromArray($method, $this->methods, []);
        $endpointArray = Utils::getFromArray($currentEndpoint, $methodArray, null);
        // echo '<pre>' . var_export($endpointArray, true) . '</pre>';

        if ($endpointArray['friendlyUrl']) {
            $arguments = empty($explodedEndpoint) ? [] : $explodedEndpoint;
            if ($method == 'post') {
                $arguments['params'] = $_POST;
            }
        } else {
            $arguments = $method == 'post' ? ['params' => $_POST] : $_GET;
        }

        // echo '<pre>' . var_export($arguments, true) . '</pre>';

        if ($endpointArray) {
            return $this->handleEndpoint($endpointArray, $arguments, $endpointArray['friendlyUrl']);
        }

        return http_response_code(404);
    }

    private function handleEndpoint(array $endpointArray, array $arguments, bool $isUrlFriendly) {
        $callback = Utils::getFromArray('callback', $endpointArray);
                
        if (is_callable($callback)) {
            return call_user_func_array($callback, $arguments);
        }

        $methodParams = [];

        foreach ($endpointArray['params'] as $key => $p) {
            if ($isUrlFriendly) {
                if (isset($arguments[$p])) {
                    $methodParams[$p] = $arguments[$p];
                } elseif (isset($arguments[$key])) {
                    $methodParams[] = $arguments[$key];
                }
            } else {
                if (!isset($arguments[$p])) {
                    $methodParams[] = null;
                    continue;
                }
                $methodParams[$p] = $arguments[$p];
                // $methodParams[$p] = isset($arguments[$p]) ? $arguments[$p] : null;
            }
        }

        if (isset($arguments['params'])) {
            $methodParams['params'] = $arguments['params'];
        }

        // echo '<pre>' . var_export($methodParams, true) . '</pre>';
        
        $controller = $endpointArray['controller'];
        $action = $endpointArray['action'];
        $obj = new $controller();

        if (is_callable([$obj, $action])) {
            return call_user_func_array([$obj, $action], $methodParams);
        }
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
        throw new \InvalidArgumentException('Controller not supplied.');
    }

    private function getAction(array $array): string {
        if (count($array) >= 2) {
            $action = $array[1];
            if (!empty($action)) {
                return $action;
            }
        }
        throw new \InvalidArgumentException('Action not supplied.');
    }

}